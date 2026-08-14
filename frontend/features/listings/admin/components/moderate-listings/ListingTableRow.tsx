import { ModeBadge } from "../../../shared/components/mode-badge/ModeBadge";
import { StatusBadge } from "../../../shared/components/status-badge/StatusBadge";
import type { AdminListingRow } from "../../types";

import { DisableListingAction } from "./DisableListingAction";

interface ListingTableRowProps {
  listing: AdminListingRow;
  onDisableToggle: (listingId: string, disabled: boolean) => void;
}

// One moderation table row (FR-018). Server leaf: renders company, listing,
// ModeBadge, and status from an AdminListingRow, composing the client-bound
// DisableListingAction leaf (structure rules §7). No approval action anywhere
// (FR-020).

export function ListingTableRow({ listing, onDisableToggle }: ListingTableRowProps) {
  return (
    <tr className="border-b border-border last:border-b-0">
      <td className="px-4 py-3 align-top">
        <p className="text-sm font-semibold text-foreground">{listing.companyDisplay}</p>
        <p className="text-xs text-muted-foreground">{listing.field}</p>
      </td>

      <td className="px-4 py-3 align-top">
        <p className="text-sm font-medium text-foreground">{listing.specialization}</p>
      </td>

      <td className="px-4 py-3 align-top">
        <ModeBadge mode={listing.mode} />
      </td>

      <td className="px-4 py-3 align-top">
        <StatusBadge status={listing.status} />
      </td>

      <td className="px-4 py-3 text-right align-top">
        <DisableListingAction
          listingId={listing.id}
          disabled={listing.disabled}
          onToggle={onDisableToggle}
        />
      </td>
    </tr>
  );
}
