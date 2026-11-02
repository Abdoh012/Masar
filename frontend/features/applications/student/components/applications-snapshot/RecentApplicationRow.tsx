import { RECENT_ROW_BADGE_CLASS } from "./constants";
import { formatApplicationDate } from "../my-applications/constants";
import type { RecentApplicationRow } from "../../types";
import { ApplicationStatusBadge } from "../ApplicationStatusBadge";

// Leaf: one recent-application row. The monogram keeps the row from being a
// bare two-line text block, and the fixed-width badge keeps the right edge of
// the column still as the status label changes length. The date arrives as the
// API's ISO-8601 and is formatted here, in the feature that owns the format.
export function RecentApplicationRow({ row }: { row: RecentApplicationRow }) {
  const dateOn = row.dateOn ? formatApplicationDate(row.dateOn) : "";

  return (
    <li className="group flex items-center gap-4 border-t border-border py-4 transition-colors duration-200 hover:bg-background/70 sm:rounded-lg sm:px-3">
      {/* Company monogram — the row data carries no logo yet. */}
      <span className="flex size-10 shrink-0 items-center justify-center rounded-lg bg-primary-tint text-sm font-semibold text-primary-text">
        {row.companyName.charAt(0)}
      </span>

      <div className="flex min-w-0 flex-1 flex-col gap-0.5">
        <p className="truncate text-sm font-semibold leading-relaxed text-foreground">
          {row.companyName}
        </p>
        <p className="truncate text-sm leading-relaxed text-muted-foreground">
          {row.listingTitle}
        </p>
      </div>

      <div className="flex shrink-0 items-center gap-4">
        {dateOn ? (
          <time className="font-mono text-xs text-muted-foreground">
            {dateOn}
          </time>
        ) : null}
        <ApplicationStatusBadge status={row.status} className={RECENT_ROW_BADGE_CLASS} />
      </div>
    </li>
  );
}
