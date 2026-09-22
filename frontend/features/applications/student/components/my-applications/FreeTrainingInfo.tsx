// FreeTrainingInfo: free-training counterpart of the paid card's trial chip —
// fills the same in-card slot on Accepted-free cards (where the countdown gap
// would otherwise sit) with quiet training facts. Mirrors the trial chip's
// container shape (rounded-xl, tinted, px-4 py-3, mt-4 rhythm) using the
// primary identity tint — no status urgency tokens apply to a free training.
// Each row renders only when its field is actually present, and the block
// returns null when nothing is available so an empty box never renders.
import {
  FREE_TRAINING_INFO_LABELS,
  formatApplicationDate,
  formatDurationDays,
  TRAINING_MODE_LABELS,
} from "./constants";

interface FreeTrainingInfoProps {
  duration: number | null;
  method?: string;
  startsAt?: string;
}

export function FreeTrainingInfo({
  duration,
  method,
  startsAt,
}: FreeTrainingInfoProps) {
  const modeLabel = method ? TRAINING_MODE_LABELS[method] : undefined;
  const startsValue = startsAt ? formatApplicationDate(startsAt) : undefined;

  const rows: { label: string; value: string }[] = [];
  if (duration !== null) {
    rows.push({ label: FREE_TRAINING_INFO_LABELS.duration, value: formatDurationDays(duration) });
  }
  if (modeLabel) {
    rows.push({ label: FREE_TRAINING_INFO_LABELS.mode, value: modeLabel });
  }
  if (startsValue) {
    rows.push({ label: FREE_TRAINING_INFO_LABELS.starts, value: startsValue });
  }

  if (rows.length === 0) return null;

  return (
    <div className="mt-4 flex flex-col gap-1.5 rounded-xl bg-primary-tint px-4 py-3">
      {rows.map((row) => (
        <div
          key={row.label}
          className="flex items-center justify-between gap-3 text-xs"
        >
          <span className="text-muted-foreground">{row.label}</span>
          <span className="font-medium text-primary-text">{row.value}</span>
        </div>
      ))}
    </div>
  );
}