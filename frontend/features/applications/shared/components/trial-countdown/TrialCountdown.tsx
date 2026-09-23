// Countdown ring — the "opportunity clock" for a bounded run-down period.
// Two things express the state: the arc is proportional to
// daysRemaining / totalDays (full ring on day one, depleting as the period
// runs down), and the color + chip tint shift through the status tokens as
// days run out (healthy = success, 4–7 days = warning, ≤3 days = critical —
// thresholds and styles in sibling constants.ts; never sage, which is
// reserved for the hire-opportunity-confirmed signal). Server-rendered, no
// live ticking — it reflects each server-side re-render. Shared by the
// dashboard's ActiveTraining (free-trial period) and the My Applications
// page's ApplicationCard (free-trial period OR remaining-training-days), the
// sibling constants provide the default trial label plus the "Training
// period" label the remaining-days countdown passes in.

import {
  TRIAL_CRITICAL_DAYS,
  TRIAL_LOW_DAYS,
  TRIAL_PERIOD_LABEL,
  TRIAL_URGENCY_STYLES,
} from "./constants";
import type { TrialUrgency } from "./constants";

// Real circumference of the r=17 ring (2π·17 ≈ 106.81) — with strokeDasharray
// set to it, offset 0 renders a closed ring and offset C an empty one.
const CIRCUMFERENCE = 2 * Math.PI * 17;

export function TrialCountdown({
  daysRemaining,
  totalDays,
  periodLabel = TRIAL_PERIOD_LABEL,
}: {
  daysRemaining: number;
  totalDays: number;
  periodLabel?: string;
}) {
  // Fraction of the trial still left, clamped to [0, 1] defensively (a null
  // or zero denominator, or remaining > total, must never break the arc).
  const fraction =
    totalDays > 0 ? Math.min(1, Math.max(0, daysRemaining / totalDays)) : 0;
  const dashOffset = Math.round(CIRCUMFERENCE * (1 - fraction) * 10) / 10;

  const urgency: TrialUrgency =
    daysRemaining <= TRIAL_CRITICAL_DAYS
      ? "critical"
      : daysRemaining <= TRIAL_LOW_DAYS
        ? "low"
        : "healthy";
  const styles = TRIAL_URGENCY_STYLES[urgency];

  return (
    <div
      className={`mt-4 inline-flex items-center gap-3 rounded-xl px-4 py-3 ${styles.chip}`}
    >
      <span className="relative size-11 shrink-0">
        <svg viewBox="0 0 40 40" className="size-11 -rotate-90" aria-hidden="true">
          <circle
            cx="20"
            cy="20"
            r="17"
            fill="none"
            strokeWidth={4}
            stroke="var(--color-primary-50)"
          />
          <circle
            cx="20"
            cy="20"
            r="17"
            fill="none"
            strokeWidth={4}
            strokeLinecap="round"
            stroke={styles.stroke}
            strokeDasharray={CIRCUMFERENCE}
            strokeDashoffset={dashOffset}
          />
        </svg>
        <span
          className={`absolute inset-0 grid place-items-center font-mono text-xs font-semibold ${styles.text}`}
        >
          {daysRemaining}
        </span>
      </span>
      <span>
        <span className={`block text-sm font-semibold ${styles.text}`}>
          {daysRemaining} days remaining
        </span>
        <span className="block text-xs text-muted-foreground">
          {periodLabel}
        </span>
      </span>
    </div>
  );
}