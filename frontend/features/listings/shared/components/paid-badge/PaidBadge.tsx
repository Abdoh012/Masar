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
  price?: number;
  currency?: string;
  className?: string;
}

function formatPrice(price: number, currency?: string): string {
  const formatted = new Intl.NumberFormat("en-US").format(price);
  return currency ? `${formatted} ${currency}` : formatted;
}

// PaidBadge: the shared "Free" / "Paid · {price} · {trialDays}d trial" pill. One
// definition reused across ListingCard, ListingDetail, ListingRow, and
// ListingTableRow (FR-023). Pure leaf.
export function PaidBadge({ isPaid, trialDays, price, currency, className }: PaidBadgeProps) {
  let label: string;
  if (!isPaid) {
    label = FREE_LABEL;
  } else {
    const pricePart = price ? formatPrice(price, currency) : "";
    const trialPart = `${trialDays ?? TRIAL_MIN_DAYS}${PAID_LABEL_TRIAL_SUFFIX}`;
    label = pricePart
      ? `${PAID_LABEL_PREFIX} · ${pricePart} · ${trialPart}`
      : `${PAID_LABEL_PREFIX} · ${trialPart}`;
  }

  return (
    <span aria-label={PAID_BADGE_ARIA_LABEL} className={`${PAID_BADGE_CLASSES} ${className ?? ""}`.trim()}>
      {label}
    </span>
  );
}