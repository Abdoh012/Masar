import { TrainingProgressBar } from "./TrainingProgressBar";

interface ActiveTrainingCardProps {
  company: string;
  listingTitle: string;
  /** Elapsed share of the run, 0..100 — computed by the orchestrator from the
   *  training's start/end dates. Null hides the track. */
  percent: number | null;
}

// ActiveTrainingCard: leaf — the active training's company + title, with the
// brand-coloured track showing how far through the run it is. Purely
// presentational: the percentage arrives as a plain number so the card holds no
// date logic of its own.
export function ActiveTrainingCard({
  company,
  listingTitle,
  percent,
}: ActiveTrainingCardProps) {
  return (
    <div className="rounded-xl border border-border bg-background p-4 transition-shadow duration-200 hover:shadow-card">
      <div className="flex items-center gap-3">
        {/* Company */}
        <span className="flex size-11 shrink-0 items-center justify-center rounded-full bg-primary-tint text-base font-semibold text-primary-text">
          {company.charAt(0)}
        </span>

        {/* Title */}
        <div className="min-w-0">
          <p className="font-mono text-xs font-semibold uppercase tracking-[0.08em] text-muted-foreground">
            {company}
          </p>
          <p className="truncate text-base font-semibold text-foreground">
            {listingTitle}
          </p>
        </div>
      </div>

      {/* Run progress — brand fill on a tinted track */}
      <TrainingProgressBar percent={percent} />
    </div>
  );
}
