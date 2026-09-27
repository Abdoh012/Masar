interface ProfileCompletionMeterProps {
  /** 0..100, straight from the backend. */
  percent: number;
}

// Leaf: the profile-completion readout — the percentage beside its label and the
// track beneath it. The visible fill is the only place the number is drawn, so
// the track and the label can never disagree. The pair is announced once as
// text and the track itself is hidden from assistive tech.
export function ProfileCompletionMeter({ percent }: ProfileCompletionMeterProps) {
  return (
    <div className="w-full min-w-44">
      <div className="flex items-baseline justify-between gap-3">
        <span className="text-xs font-semibold uppercase tracking-[0.08em] text-muted-foreground">
          Profile completion
        </span>
        <span className="text-sm font-semibold tabular-nums text-primary-text">
          {percent}%
        </span>
      </div>

      <div
        role="progressbar"
        aria-valuenow={percent}
        aria-valuemin={0}
        aria-valuemax={100}
        aria-label="Profile completion"
        className="mt-2 h-1.5 overflow-hidden rounded-full bg-primary-tint"
      >
        <div
          className="h-full rounded-full bg-primary transition-[width] duration-300"
          style={{ width: `${percent}%` }}
        />
      </div>
    </div>
  );
}
