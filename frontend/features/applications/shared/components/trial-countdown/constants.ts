// Urgency styling for the trial countdown (shared component config — §14).
// The ring + chip shift through the status tokens as the trial runs out —
// never sage, which is reserved exclusively for the hire-opportunity-confirmed
// signal. `*-bg`/`*-fg` semantic pairs adapt to dark mode via the theme's
// color-mix overrides; the 500 strokes are fixed brand status colors that read
// on both themes.

// Absolute days remaining at which the urgency escalates. A 3-day-left on a
// 30-day trial is still "critical" even though its arc would be ~90% full, so
// the thresholds key on the count itself, not on the fraction.
export const TRIAL_LOW_DAYS = 7;
export const TRIAL_CRITICAL_DAYS = 3;

export type TrialUrgency = "healthy" | "low" | "critical";

export const TRIAL_URGENCY_STYLES: Record<
  TrialUrgency,
  { stroke: string; chip: string; text: string }
> = {
  healthy: {
    stroke: "var(--color-success-500)",
    chip: "bg-success-bg",
    text: "text-success-fg",
  },
  low: {
    stroke: "var(--color-warning-500)",
    chip: "bg-warning-bg",
    text: "text-warning-fg",
  },
  critical: {
    stroke: "var(--color-error-500)",
    chip: "bg-error-bg",
    text: "text-error-fg",
  },
};