// PaymentStatusBadge: the "Free" / "Paid" pill on Applied and Accepted
// application cards — the two card types whose backend DTO actually sends
// `is_paid` (application_cards.php); Rejected/Withdrawn cards don't carry the
// field, so they render no pill rather than defaulting to a lie. Free uses the
// success status token; paid uses the seal-gold secondary tint so a paid
// card's "Paid" pill never blends into the primary-tint "May lead to hire"
// pill beside it. Pure leaf — label + tints from sibling constants, mirroring
// the listings PaidBadge concept (§14).
import {
  PAYMENT_BADGE_LABELS,
  PAYMENT_BADGE_STYLES,
} from "./constants";

interface PaymentStatusBadgeProps {
  isPaid: boolean;
}

export function PaymentStatusBadge({ isPaid }: PaymentStatusBadgeProps) {
  const label = isPaid ? PAYMENT_BADGE_LABELS.paid : PAYMENT_BADGE_LABELS.free;
  const ariaLabel = isPaid
    ? PAYMENT_BADGE_LABELS.ariaPaid
    : PAYMENT_BADGE_LABELS.ariaFree;

  return (
    <span
      aria-label={ariaLabel}
      className={
        "rounded-full px-2.5 py-0.5 text-xs font-medium " +
        PAYMENT_BADGE_STYLES[isPaid ? "paid" : "free"]
      }
    >
      {label}
    </span>
  );
}