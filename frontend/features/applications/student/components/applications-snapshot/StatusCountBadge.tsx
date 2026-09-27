import {
  STATUS_BADGE_CLASSES,
  STATUS_COUNT_ACCENT_CLASSES,
  STATUS_COUNT_ICONS,
  STATUS_RAIL_CLASSES,
} from "./constants";
import type { ApplicationStatus } from "../../types";

export interface StatusCountBadgeProps {
  label: string;
  count: number;
  status: ApplicationStatus;
}

// Leaf: one status count tile. The count is the subject — it sits at the top of
// the tile at display size, with the label beneath it as a quiet caption, and
// the status icon beside it as support rather than as the focal point.
//
// The semantic colour is a full-height rail down the left edge instead of a
// tinted panel: four coloured blocks side by side read as a colour chart, while
// a rail keeps the surface neutral so the four counts can be compared as
// numbers. The icon disc reuses the pill's own bg/text pair so the tile and the
// badge elsewhere can never disagree.
//
// Compact by construction: no min-height and a horizontal layout, so a row of
// four is one tight band instead of four tall boxes with dead space under the
// label. `tabular-nums` stops the tile jittering as a count changes digits, and
// the `overflow-hidden` clips the rail to the tile's rounded corners.
export function StatusCountBadge({
  label,
  count,
  status,
}: StatusCountBadgeProps) {
  const Icon = STATUS_COUNT_ICONS[status];

  return (
    <div className="group relative flex items-center justify-between gap-3.5 overflow-hidden rounded-xl border border-border bg-background p-4 shadow-card transition-shadow duration-200 hover:shadow-card-md">
      <span
        className={
          "absolute inset-y-0 left-0 w-1 " + STATUS_RAIL_CLASSES[status]
        }
      />

      <div className="flex min-w-0 flex-col">
        <span
          className={
            "text-3xl font-semibold leading-none tracking-tight tabular-nums " +
            STATUS_COUNT_ACCENT_CLASSES[status]
          }
        >
          {count}
        </span>
        <span className="mt-1.5 truncate text-xs font-medium uppercase tracking-wide text-muted-foreground">
          {label}
        </span>
      </div>
      <span
        className={
          "flex size-10 shrink-0 items-center justify-center rounded-xl " +
          STATUS_BADGE_CLASSES[status]
        }
      >
        <Icon className="size-5" />
      </span>
    </div>
  );
}
