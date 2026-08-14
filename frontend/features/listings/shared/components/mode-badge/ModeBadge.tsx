import type { ListingMode } from "../../types";
import { MODE_LABELS } from "../../lib/constants";
import { MODE_BADGE_ARIA_LABEL, MODE_BADGE_CLASSES } from "./constants";

interface ModeBadgeProps {
  mode: ListingMode;
  className?: string;
}

// ModeBadge: the shared Observer / Hands-on / Project-based pill. One
// definition reused across ListingCard, ListingDetail, ListingRow, and
// ListingTableRow (FR-023). Pure leaf — label from the shared option list.
export function ModeBadge({ mode, className }: ModeBadgeProps) {
  return (
    <span aria-label={MODE_BADGE_ARIA_LABEL} className={`${MODE_BADGE_CLASSES} ${className ?? ""}`.trim()}>
      {MODE_LABELS[mode]}
    </span>
  );
}