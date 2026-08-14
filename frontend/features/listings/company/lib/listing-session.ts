"use client";

import { useSyncExternalStore } from "react";

import type { MyListingRow } from "../types";

// Feature-scoped in-memory session store for the company's listing lifecycle
// (FR-006 / FR-010). Create, edit, and close are local UI-state operations in
// this UI-only phase: upserted/closed listings live here for the current UI
// session and are cleared on full reload. Nothing is persisted, and this is
// never app-global state (R-4).

let sessionListings: MyListingRow[] = [];

const listeners = new Set<() => void>();

function emit() {
  for (const listener of listeners) listener();
}

function subscribe(listener: () => void) {
  listeners.add(listener);
  return () => listeners.delete(listener);
}

export function getSessionListings(): MyListingRow[] {
  return sessionListings;
}

export function upsertSessionListing(row: MyListingRow): void {
  const index = sessionListings.findIndex((existing) => existing.id === row.id);
  if (index === -1) {
    sessionListings = [...sessionListings, row];
  } else {
    sessionListings = sessionListings.map((existing, i) => (i === index ? row : existing));
  }
  emit();
}

export function closeSessionListing(id: string): void {
  sessionListings = sessionListings.map((listing) =>
    listing.id === id ? { ...listing, status: "closed" } : listing,
  );
  emit();
}

// Client hook: renders the current session list and resubscribes on change.
export function useSessionListings(): MyListingRow[] {
  return useSyncExternalStore(subscribe, getSessionListings, getSessionListings);
}