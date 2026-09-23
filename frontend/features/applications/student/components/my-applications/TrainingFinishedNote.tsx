// TrainingFinishedNote: replaces the remaining-days countdown once the
// training has 0 days left — the run-down has reached its end, so instead of
// a "0 days remaining" critical chip the card states the completion plainly.
// Mirrors the countdown chip shape (offset with mt-4, icon + bold line + muted
// subline) in the info tokens: finishing is a neutral milestone, not urgency,
// and never sage (that stays reserved for hire-opportunity-confirmed).
import { GraduationCap } from "lucide-react";

import { TRAINING_FINISHED_NOTE } from "./constants";

export function TrainingFinishedNote() {
  return (
    <div className="mt-4 inline-flex items-center gap-3 rounded-xl bg-info-bg px-4 py-3">
      <GraduationCap className="size-5 shrink-0 text-info-fg" aria-hidden="true" />
      <span>
        <span className="block text-sm font-semibold text-info-fg">
          {TRAINING_FINISHED_NOTE.title}
        </span>
        <span className="block text-xs text-muted-foreground">
          {TRAINING_FINISHED_NOTE.subline}
        </span>
      </span>
    </div>
  );
}