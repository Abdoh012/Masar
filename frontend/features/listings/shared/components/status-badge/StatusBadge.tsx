import type { ListingStatus } from "../../types";
import { STATUS_LABELS } from "../../lib/constants";
import { STATUS_BADGE_ARIA_LABEL, STATUS_BADGE_CLASSES } from "./constants";

interface StatusBadgeProps {
  status: ListingStatus;
  className?: string;
}

// StatusBadge: the shared Draft / Published / Closed pill. One definition
// reused across ListingRow (company) and ListingTableRow (admin) — mirroring
// the ModeBadge/PaidBadge pattern. Pure leaf — label from the shared status
// map, no state, no fetching.
export function StatusBadge({ status, className }: StatusBadgeProps) {
  return (
    <span aria-label={STATUS_BADGE_ARIA_LABEL} className={`${STATUS_BADGE_CLASSES} ${className ?? ""}`.trim()}>
      {STATUS_LABELS[status]}
    </span>
  );
}