import { ModeBadge } from "../../../shared/components/mode-badge/ModeBadge";
import { StatusBadge } from "../../../shared/components/status-badge/StatusBadge";
import type { MyListingRow } from "../../types";

import { ListingRowActions } from "./ListingRowActions";
import { ROW_LABELS } from "./constants";

interface ListingRowProps {
  listing: MyListingRow;
}

// One My Listings row (FR-007). Server leaf: renders status, ModeBadge,
// applicant count, and posted date from a MyListingRow, then composes the
// client-bound ListingRowActions leaf for the row's Edit/Close/View
// Applicants actions (structure rules §7).

export function ListingRow({ listing }: ListingRowProps) {
  return (
    <div className="flex flex-col gap-4 rounded-xl border border-border bg-card p-5 sm:flex-row sm:items-center sm:justify-between">
      <div className="min-w-0 space-y-2">
        <div className="flex flex-wrap items-center gap-2">
          <StatusBadge status={listing.status} />
          <ModeBadge mode={listing.mode} />
          <span className="text-sm text-muted-foreground">{listing.specialization}</span>
        </div>

        <p className="truncate font-sans text-base font-semibold text-foreground">
          {listing.companyName}
        </p>

        <p className="text-xs text-muted-foreground">
          {ROW_LABELS.applicantCount(listing.applicantCount)} · Posted {listing.postedDate}
        </p>
      </div>

      <ListingRowActions listingId={listing.id} status={listing.status} />
    </div>
  );
}
