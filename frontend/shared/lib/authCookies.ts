// Framework-free auth-cookie plumbing, safe for the Edge runtime.
//
// It deliberately lives outside `services/cookies.ts` because that module
// imports "server-only" and `next/headers`, neither of which can be pulled into
// `middleware.ts`. Both sides import these names/constants from here so the
// cookie names, lifetimes and Set-Cookie parsing have a single definition.

export const ACCESS_TOKEN_COOKIE = "masarJwt";
export const ROLE_COOKIE = "masarRole";
export const COMPANY_STATUS_COOKIE = "companyStatus";
export const REFRESH_TOKEN_COOKIE = "refresh_token";
export const CSRF_TOKEN_COOKIE = "csrf_token";

export const ACCESS_TOKEN_MAX_AGE = 60 * 60 * 24 * 90;
export const REFRESH_TOKEN_MAX_AGE = 60 * 60 * 24 * 90;

export const SESSION_COOKIES = [
  ACCESS_TOKEN_COOKIE,
  ROLE_COOKIE,
  COMPANY_STATUS_COOKIE,
  REFRESH_TOKEN_COOKIE,
  CSRF_TOKEN_COOKIE,
];

export type BackendCookie = { name: string; value: string };

// Splits a possibly comma-joined `set-cookie` header back into individual
// cookies. The lookahead only breaks on `, name=`, so the comma inside an
// `Expires=Wed, 09 Jun 2027 …` attribute is left alone.
const SET_COOKIE_SEPARATOR = /,(?=\s*[^;=,\s]+=[^;]*)/;

// Reads the `Set-Cookie` headers a browser would have received, so the
// server-side flow can forward the cookies the backend mints at runtime (the
// access/refresh/CSRF pair). Prefers `getSetCookie()` (Node 20+/Edge undici) and
// falls back to the joined `set-cookie` header.
export function parseSetCookie(res: Response): BackendCookie[] {
  const raw =
    typeof res.headers.getSetCookie === "function"
      ? res.headers.getSetCookie()
      : [res.headers.get("set-cookie")].filter((h): h is string => Boolean(h));

  const result: BackendCookie[] = [];
  for (const header of raw.flatMap((h) => h.split(SET_COOKIE_SEPARATOR))) {
    const pair = header.split(";", 1)[0];
    const eq = pair.indexOf("=");
    if (eq <= 0) continue;
    result.push({
      name: pair.slice(0, eq).trim(),
      value: pair.slice(eq + 1).trim(),
    });
  }
  return result;
}

// Rewrites a raw `Cookie` request header so the listed names carry the given
// values and every other cookie is preserved untouched. Used by middleware to
// hand the Server Component render a request that already carries a freshly
// rotated token, without waiting for the browser to round-trip the new cookie.
export function replaceCookiesInHeader(
  header: string | null,
  updates: Record<string, string>,
): string {
  const names = Object.keys(updates);
  const kept = (header ?? "")
    .split(";")
    .map((pair) => pair.trim())
    .filter(Boolean)
    .filter((pair) => !names.some((name) => pair.startsWith(`${name}=`)));

  for (const [name, value] of Object.entries(updates)) {
    kept.push(`${name}=${value}`);
  }
  return kept.join("; ");
}
