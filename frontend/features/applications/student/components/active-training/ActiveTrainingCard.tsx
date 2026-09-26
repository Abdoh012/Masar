import { cn } from "@/shared/lib/utils";

interface ActiveTrainingCardProps {
  company: string;
  listingTitle: string;
  // Present only while a trial is running; both undefined for the
  // part-time/full-time presentation, which shows no progress track.
  daysRemaining?: number;
  totalDays?: number;
}

// Leaf: the active training's company + title, with a brand-coloured track
// showing how far through the trial the run is. The track is decorative —
// TrialCountdown below already states the day count out loud, so announcing
// the same number twice would just be noise.
export default function ActiveTrainingCard({
  company,
  listingTitle,
  daysRemaining,
  totalDays,
}: ActiveTrainingCardProps) {
  const progress =
    totalDays && daysRemaining != null
      ? Math.min(100, Math.max(0, (daysRemaining / totalDays) * 100))
      : null;

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

      {/* Trial progress — brand fill on a tinted track */}
      {progress != null ? (
        <div
          aria-hidden="true"
          className={cn(
            "mt-4 h-1.5 overflow-hidden rounded-full",
            "bg-primary-tint",
          )}
        >
          <div
            className="h-full rounded-full bg-primary transition-[width] duration-200"
            style={{ width: `${progress}%` }}
          />
        </div>
      ) : null}
    </div>
  );
}
