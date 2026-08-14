import { PENDING_APPROVAL } from "./constants";

// PendingStatusBadge: the "Pending Review" pill that sits under the card
// description — the established tinted-pill pattern (bg-*-bg text-*-fg)
// with a gently pulsing amber dot to hint the account is actively awaiting
// review, not failed.
export function PendingStatusBadge() {
  return (
    <span className="inline-flex items-center gap-2 rounded-full bg-warning-bg px-3 py-1 text-xs font-medium text-warning-fg">
      <span
        aria-hidden="true"
        className="size-1.5 animate-pulse rounded-full bg-warning-500"
      />
      {PENDING_APPROVAL.badgeLabel}
    </span>
  );
}
