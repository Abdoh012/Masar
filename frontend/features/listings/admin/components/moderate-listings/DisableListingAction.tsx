"use client";

import { MODERATION_LABELS } from "./constants";

interface DisableListingActionProps {
  listingId: string;
  disabled: boolean;
  onToggle: (listingId: string, disabled: boolean) => void;
}

// Per-row disable toggle for the moderation table (FR-019). "use client":
// flips a local UI flag via the parent's state only — no moderation API call,
// no network. The cross-surface browse impact of Disable is deferred to
// backend integration; this phase it is a session-local interaction only
// (R-6/R-8).

export function DisableListingAction({ listingId, disabled, onToggle }: DisableListingActionProps) {
  return (
    <button
      type="button"
      onClick={() => onToggle(listingId, !disabled)}
      aria-pressed={disabled}
      className={`cursor-pointer rounded-md border px-3 py-1.5 text-sm font-medium transition-colors ${
        disabled
          ? "border-border bg-card text-muted-foreground"
          : "border-error-500/40 bg-error-500 text-white hover:bg-error-500/90"
      }`}
    >
      {disabled ? MODERATION_LABELS.disabled : MODERATION_LABELS.disable}
    </button>
  );
}
