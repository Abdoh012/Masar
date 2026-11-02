interface TrainingProgressBarProps {
  /** Elapsed share of the run, 0..100. Null renders no track at all — the
   *  caller has no usable dates, and a bar at an invented 0% would misstate
   *  the training's state. */
  percent: number | null;
}

// TrainingProgressBar: leaf — the elapsed-share track. The visible fill is
// brand-coloured on a tinted track; a value already reads as the percentage in
// the card's surrounding copy, so the bar itself is decorative rather than
// announced twice.
export function TrainingProgressBar({ percent }: TrainingProgressBarProps) {
  if (percent == null) return null;

  return (
    <div
      aria-hidden="true"
      className="mt-4 h-1.5 overflow-hidden rounded-full bg-primary-tint"
    >
      <div
        className="h-full rounded-full bg-primary transition-[width] duration-200"
        style={{ width: `${percent}%` }}
      />
    </div>
  );
}
