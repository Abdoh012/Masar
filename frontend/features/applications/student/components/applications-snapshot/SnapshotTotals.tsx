import { SNAPSHOT_TOTALS_LABELS } from "./constants";

interface SnapshotTotalsProps {
  total: number;
  acceptanceRate: number;
}

// Leaf: the snapshot's headline figures — how many applications the student has
// ever submitted, and what share of them were accepted. Both numbers come
// straight off the API (`applications_snapshot.total` / `.acceptance_rate`),
// so this is where the section finally reports the two figures the four status
// tiles can't show on their own: the tiles break the total down but never
// state it, and nothing else in the section carried the acceptance rate.
//
// Rendered as a quiet hairline-separated pair rather than a second stat card,
// so it reads as a caption on the section rather than competing with the tiles
// below it.
export function SnapshotTotals({ total, acceptanceRate }: SnapshotTotalsProps) {
  return (
    <div className="flex shrink-0 items-center gap-3 text-sm">
      <span className="font-semibold tabular-nums text-foreground">{total}</span>
      <span className="text-muted-foreground">{SNAPSHOT_TOTALS_LABELS.total}</span>

      <span aria-hidden="true" className="h-3.5 w-px bg-border" />

      <span className="font-semibold tabular-nums text-foreground">
        {acceptanceRate}%
      </span>
      <span className="text-muted-foreground">
        {SNAPSHOT_TOTALS_LABELS.acceptanceRate}
      </span>
    </div>
  );
}
