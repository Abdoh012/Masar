// Static trial countdown — the identity's gold "opportunity clock" ring.
// Server-rendered, no live ticking. Shared by the dashboard's ActiveTraining
// and the My Applications page's ApplicationCard (second consumer → promoted
// per the promote-on-second-use rule).

export function TrialCountdown({ daysRemaining }: { daysRemaining: number }) {
  return (
    <div className="mt-4 inline-flex items-center gap-3 rounded-xl bg-secondary-tint/50 px-4 py-3">
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
            stroke="var(--color-secondary-500)"
            strokeDasharray="100"
            strokeDashoffset="22"
          />
        </svg>
        <span className="absolute inset-0 grid place-items-center font-mono text-xs font-semibold text-secondary-text">
          {daysRemaining}
        </span>
      </span>
      <span>
        <span className="block text-sm font-semibold text-secondary-text">
          {daysRemaining} days remaining
        </span>
        <span className="block text-xs text-muted-foreground">Free trial period</span>
      </span>
    </div>
  );
}