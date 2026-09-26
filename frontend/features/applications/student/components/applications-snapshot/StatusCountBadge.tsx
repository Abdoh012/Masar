import { STATUS_BADGE_CLASSES, STATUS_COUNT_ACCENT_CLASSES, STATUS_COUNT_ICONS } from "./constants";
import type { ApplicationStatus } from "../../types";

export interface StatusCountBadgeProps {
  label: string;
  count: number;
  status: ApplicationStatus;
}

// Leaf: one status count tile. A neutral lifted panel rather than a solid block
// of colour — the icon and the number carry the status, so four tiles can sit
// side by side without the section reading as a colour chart. The icon reuses
// the pill's own bg/text pair, so tile and badge can never disagree.
export function StatusCountBadge({ label, count, status }: StatusCountBadgeProps) {
  const Icon = STATUS_COUNT_ICONS[status];

  return (
    <div className="flex min-h-28 flex-col justify-between gap-3 rounded-xl border border-border bg-background p-4 shadow-card transition-shadow duration-200 hover:shadow-card-md">
      <span
        className={
          "flex size-9 items-center justify-center rounded-lg " +
          STATUS_BADGE_CLASSES[status]
        }
      >
        <Icon aria-hidden="true" className="size-4" />
      </span>

      <div className="flex flex-col gap-1">
        <span
          className={
            "text-3xl font-semibold leading-none tracking-tight " +
            STATUS_COUNT_ACCENT_CLASSES[status]
          }
        >
          {count}
        </span>
        <span className="text-xs font-medium text-muted-foreground">{label}</span>
      </div>
    </div>
  );
}
