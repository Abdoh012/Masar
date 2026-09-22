// AwaitingResponseNote: fills the empty slot inside Applied cards — they
// render no trial/facts/motivational section, so the area between the meta
// row and the actions reads as a gap. Mirrors the TrialCountdown chip shape
// (icon + bold line + muted subline, offset with mt-4) but with the
// informational status tokens: "being reviewed" is a pending state, never
// urgency, so info — not warning, and never sage.
import { Clock } from "lucide-react";

import { AWAITING_RESPONSE_NOTE } from "./constants";

export function AwaitingResponseNote() {
  return (
    <div className="mt-4 inline-flex items-center gap-3 rounded-xl bg-info-bg px-4 py-3">
      <Clock className="size-5 shrink-0 text-info-fg" aria-hidden="true" />
      <span>
        <span className="block text-sm font-semibold text-info-fg">
          {AWAITING_RESPONSE_NOTE.title}
        </span>
        <span className="block text-xs text-muted-foreground">
          {AWAITING_RESPONSE_NOTE.subline}
        </span>
      </span>
    </div>
  );
}