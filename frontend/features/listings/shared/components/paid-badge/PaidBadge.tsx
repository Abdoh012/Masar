import { TRIAL_MIN_DAYS } from "../../lib/constants";
import {
  FREE_LABEL,
  PAID_BADGE_ARIA_LABEL,
  PAID_BADGE_CLASSES,
  PAID_LABEL_PREFIX,
  PAID_LABEL_TRIAL_SUFFIX,
} from "./constants";

interface PaidBadgeProps {
  isPaid: boolean;
  trialDays?: number;
  className?: string;
}

// PaidBadge: the shared "Free" / "Paid · {trialDays}d trial" pill. One
// definition reused across ListingCard, ListingDetail, ListingRow, and
// ListingTableRow (FR-023). Pure leaf.
export function PaidBadge({ isPaid, trialDays, className }: PaidBadgeProps) {
  const label = isPaid
    ? `${PAID_LABEL_PREFIX} · ${trialDays ?? TRIAL_MIN_DAYS}${PAID_LABEL_TRIAL_SUFFIX}`
    : FREE_LABEL;

  return (
    <span aria-label={PAID_BADGE_ARIA_LABEL} className={`${PAID_BADGE_CLASSES} ${className ?? ""}`.trim()}>
      {label}
    </span>
  );
}