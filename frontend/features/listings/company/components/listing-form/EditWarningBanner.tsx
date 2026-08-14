import { AlertCircle } from "lucide-react";

import { EDIT_WARNING } from "./constants";

// Client-bound presentational leaf — no directive required. Shown only in
// edit mode when the listing already has applicants (FR-005): editing
// notifies them, so the warning must precede the form. Uses the semantic
// info alert styling, not the sage "hire confirmed" color (design system).

export function EditWarningBanner() {
  return (
    <div
      role="status"
      className="flex items-start gap-3 rounded-lg border border-info-fg/30 bg-info-bg p-4"
    >
      <AlertCircle className="mt-0.5 size-5 shrink-0 text-info-fg" aria-hidden="true" />
      <div className="space-y-1">
        <p className="text-sm font-semibold text-info-fg">{EDIT_WARNING.title}</p>
        <p className="text-sm text-foreground">{EDIT_WARNING.message}</p>
      </div>
    </div>
  );
}
