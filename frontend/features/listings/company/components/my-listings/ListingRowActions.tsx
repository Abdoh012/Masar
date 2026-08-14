"use client";

import Link from "next/link";

import { closeSessionListing } from "../../lib/listing-session";

import { ROW_LABELS } from "./constants";

interface ListingRowActionsProps {
  listingId: string;
  status: "draft" | "published" | "closed";
}

// Row actions for My Listings (FR-009/010/011). Edit and View Applicants are
// plain links (server-safe); Close is the one interactive action and writes to
// the UI-only session store (FR-010, R-4). Closing an already-closed row is
// idempotent — the button is omitted so there is nothing to re-click (R-6).
// The cross-surface browse impact of Close is deferred until backend
// integration; this phase Close affects session state only.

export function ListingRowActions({ listingId, status }: ListingRowActionsProps) {
  return (
    <div className="flex flex-wrap items-center gap-2">
      <Link
        href={`/company/listings/${listingId}`}
        className="text-sm font-medium text-primary-text transition-colors hover:underline"
      >
        {ROW_LABELS.edit}
      </Link>

      {status !== "closed" ? (
        <button
          type="button"
          onClick={() => closeSessionListing(listingId)}
          className="text-sm font-medium text-error-fg transition-colors hover:underline"
        >
          {ROW_LABELS.close}
        </button>
      ) : (
        <span className="text-sm font-medium text-muted-foreground">
          {ROW_LABELS.closeUnavailable}
        </span>
      )}

      <Link
        href={`/company/listings/${listingId}/applicants`}
        className="text-sm font-medium text-primary-text transition-colors hover:underline"
      >
        {ROW_LABELS.viewApplicants}
      </Link>
    </div>
  );
}
