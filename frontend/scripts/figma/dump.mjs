#!/usr/bin/env node
// figma/dump.mjs — one-shot, READ-ONLY inspection of a Figma file (phase 1).
// Fetches the file index (pages, top-level frames + dimensions) plus any
// design variables/styles, and writes derived, token-free JSON under
// frontend/design/figma/. It never writes the token to disk or stdout.
//
// Auth: FIGMA_TOKEN + FIGMA_FILE_KEY from frontend/.figma.env (or process env).
//
// Usage:
//   node scripts/figma/dump.mjs [--file-key <key>] [--out <dir>] [--save-raw]

import { mkdirSync, readFileSync, writeFileSync } from "node:fs";
import path from "node:path";
import { fileURLToPath } from "node:url";

const FIGMA_BASE = process.env.FIGMA_BASE || "https://api.figma.com/v1";

const scriptDir = path.dirname(fileURLToPath(import.meta.url));
const FRONTEND_ROOT = path.resolve(scriptDir, "..", "..");
const ENV_PATH = path.join(FRONTEND_ROOT, ".figma.env");
const DEFAULT_OUT = path.join(FRONTEND_ROOT, "design", "figma");

function printHelp() {
  console.log(`figma/dump.mjs — Figma whole-file inspection (read-only)

Reads FIGMA_TOKEN + FIGMA_FILE_KEY from ${ENV_PATH} (or process env).

Options:
  --file-key <key>   Override the FIGMA_FILE_KEY from env
  --out <dir>        Output directory (default: ${DEFAULT_OUT})
  --save-raw         Also store the raw /files response under <out>/raw/file.json
  -h, --help         Show this help`);
}

function parseArgs(argv) {
  const opts = { out: DEFAULT_OUT, saveRaw: false, fileKey: null };
  for (let i = 0; i < argv.length; i++) {
    const arg = argv[i];
    if (arg === "--out" || arg === "-o") {
      opts.out = argv[++i];
      if (opts.out === undefined) throw new Error("--out requires a value");
    } else if (arg === "--file-key") {
      opts.fileKey = argv[++i];
      if (opts.fileKey === undefined) throw new Error("--file-key requires a value");
    } else if (arg === "--save-raw") {
      opts.saveRaw = true;
    } else if (arg === "--help" || arg === "-h") {
      printHelp();
      process.exit(0);
    } else {
      throw new Error(`Unknown argument: ${arg}`);
    }
  }
  return opts;
}

function loadEnvFile(filePath) {
  const out = {};
  let raw;
  try {
    raw = readFileSync(filePath, "utf8");
  } catch {
    return out;
  }
  for (const line of raw.split(/\r?\n/)) {
    const trimmed = line.trim();
    if (!trimmed || trimmed.startsWith("#")) continue;
    const eq = trimmed.indexOf("=");
    if (eq === -1) continue;
    const key = trimmed.slice(0, eq).trim();
    let value = trimmed.slice(eq + 1).trim();
    if (
      (value.startsWith('"') && value.endsWith('"')) ||
      (value.startsWith("'") && value.endsWith("'"))
    ) {
      value = value.slice(1, -1);
    }
    if (key) out[key] = value;
  }
  return out;
}

const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

async function figmaGet(pathname, token) {
  const res = await fetch(`${FIGMA_BASE}${pathname}`, {
    headers: { "X-Figma-Token": token },
  });
  const text = await res.text();
  let body = null;
  try {
    body = JSON.parse(text);
  } catch {
    body = null;
  }
  if (!res.ok) {
    const err = body && body.err ? body.err : `HTTP ${res.status}`;
    throw new Error(`${err} (GET ${pathname})`);
  }
  return body;
}

function collectFrames(page) {
  const frames = [];
  for (const child of page.children || []) {
    const box = child.absoluteBoundingBox;
    frames.push({
      id: child.id,
      name: child.name,
      type: child.type,
      width: box ? box.width : null,
      height: box ? box.height : null,
      x: box ? Math.round(box.x) : null,
      y: box ? Math.round(box.y) : null,
    });
  }
  return frames;
}

function buildPages(document) {
  const pages = [];
  for (const page of document.children || []) {
    const frames = collectFrames(page);
    pages.push({
      id: page.id,
      name: page.name,
      frameCount: frames.length,
      frames,
    });
  }
  return pages;
}

function normalizeVariables(meta) {
  if (!meta || !meta.variables || !meta.variableCollections) return null;
  const byId = new Map();
  const collections = Object.values(meta.variableCollections).map((c) => {
    const entry = {
      id: c.id,
      name: c.name,
      defaultModeId: c.defaultModeId,
      modes: (c.modes || []).map((m) => ({ id: m.modeId, name: m.name })),
      variables: [],
    };
    byId.set(c.id, entry);
    return entry;
  });
  for (const v of Object.values(meta.variables)) {
    const collection = byId.get(v.variableCollectionId);
    if (!collection) continue;
    collection.variables.push({
      id: v.id,
      name: v.name,
      type: v.resolvedType,
      scopes: v.scopes || [],
      values: v.valuesByMode || {},
    });
  }
  const total = collections.reduce((n, c) => n + c.variables.length, 0);
  const countsByType = {};
  for (const c of collections) {
    for (const v of c.variables) {
      countsByType[v.type] = (countsByType[v.type] || 0) + 1;
    }
  }
  return { collections, total, countsByType };
}

function normalizeStyles(meta) {
  if (!meta || !meta.styles) return null;
  const byType = {};
  for (const s of Object.values(meta.styles)) {
    const group = byType[s.styleType] || (byType[s.styleType] = []);
    group.push({ id: s.id, key: s.key, name: s.name });
  }
  return { byType, total: Object.keys(meta.styles).length };
}

async function run() {
  const opts = parseArgs(process.argv.slice(2));
  const fileEnv = loadEnvFile(ENV_PATH);
  const token = process.env.FIGMA_TOKEN || fileEnv.FIGMA_TOKEN;
  const fileKey = opts.fileKey || process.env.FIGMA_FILE_KEY || fileEnv.FIGMA_FILE_KEY;

  if (!token) {
    throw new Error(
      `FIGMA_TOKEN not found. Add it to ${ENV_PATH} (see .figma.env.example) or set it in the environment.`,
    );
  }
  if (!fileKey) {
    throw new Error(
      `FIGMA_FILE_KEY not found. Add it to ${ENV_PATH} or pass --file-key.`,
    );
  }

  const outDir = path.resolve(opts.out);
  mkdirSync(outDir, { recursive: true });
  mkdirSync(path.join(outDir, "raw"), { recursive: true });

  const keyPath = encodeURIComponent(fileKey);

  const file = await figmaGet(`/files/${keyPath}`, token);
  await sleep(300);
  const variablesRes = await figmaGet(`/files/${keyPath}/variables/local`, token).catch(
    (err) => ({ _error: err instanceof Error ? err.message : String(err) }),
  );
  await sleep(300);
  const stylesRes = await figmaGet(`/files/${keyPath}/styles`, token).catch(
    (err) => ({ _error: err instanceof Error ? err.message : String(err) }),
  );

  const pages = buildPages(file.document || {});
  const tokens = "_error" in variablesRes ? null : normalizeVariables(variablesRes && variablesRes.meta);
  const styles = "_error" in stylesRes ? null : normalizeStyles(stylesRes && stylesRes.meta);

  const meta = {
    source: "Figma REST API (files, files/{key}/variables/local, files/{key}/styles)",
    file: {
      key: fileKey,
      name: file.name ?? null,
      editorType: file.editorType ?? null,
      lastModified: file.lastModified ?? null,
      version: file.version ?? null,
      thumbnailUrl: file.thumbnailUrl ?? null,
    },
    extractedAt: new Date().toISOString(),
    pages: {
      count: pages.length,
      frameCount: pages.reduce((n, p) => n + p.frameCount, 0),
    },
    variables: tokens
      ? { collections: tokens.collections.length, total: tokens.total, countsByType: tokens.countsByType }
      : { error: variablesRes._error ?? "unavailable" },
    styles: styles
      ? { total: styles.total, countsByType: Object.fromEntries(Object.entries(styles.byType).map(([k, v]) => [k, v.length])) }
      : { error: stylesRes._error ?? "unavailable" },
  };

  writeFileSync(path.join(outDir, "meta.json"), JSON.stringify(meta, null, 2) + "\n");
  writeFileSync(path.join(outDir, "pages.json"), JSON.stringify(pages, null, 2) + "\n");
  writeFileSync(path.join(outDir, "tokens.json"), JSON.stringify(tokens, null, 2) + "\n");
  writeFileSync(path.join(outDir, "styles.json"), JSON.stringify(styles, null, 2) + "\n");

  if (opts.saveRaw) {
    writeFileSync(path.join(outDir, "raw", "file.json"), JSON.stringify(file, null, 2) + "\n");
  }

  console.log(`\nExtracted "${meta.file.name}" (key: ${fileKey})`);
  console.log(`  pages:        ${meta.pages.count}`);
  console.log(`  top-level frames: ${meta.pages.frameCount}`);
  console.log(
    tokens ? `  variables:    ${meta.variables.total} / ${meta.variables.collections} collections` : `  variables:    unavailable (${meta.variables.error})`,
  );
  console.log(
    styles ? `  styles:       ${meta.styles.total}` : `  styles:       unavailable (${meta.styles.error})`,
  );
  console.log(`\nWrote: ${outDir}\\{meta,pages,tokens,styles}.json`);
  if (opts.saveRaw) console.log(`       ${outDir}\\raw\\file.json`);
  console.log("No application code was read or modified. Token was never written.\n");
}

run().catch((err) => {
  console.error(`\nfigma/dump.mjs failed: ${err.message}\n`);
  process.exit(1);
});