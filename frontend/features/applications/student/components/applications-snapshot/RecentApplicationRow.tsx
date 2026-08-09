import { STATUS_BADGE_CLASSES } from "./constants";
import type { RecentApplicationRow } from "../../types";

// Leaf: one recent-application row.
export function RecentApplicationRow({ row }: { row: RecentApplicationRow }) {
  return (
    <li className="flex flex-col gap-1.5 border-t border-border py-3 sm:flex-row sm:items-center sm:justify-between sm:gap-3">
      <div className="flex min-w-0 flex-col gap-0.5">
        <p className="truncate text-sm font-medium text-foreground">{row.companyName}</p>
        <p className="truncate text-xs text-muted-foreground">{row.listingTitle}</p>
      </div>
      <div className="flex shrink-0 items-center gap-3">
        <time className="font-mono text-xs text-muted-foreground">{row.appliedOn}</time>
        <span
          className={
            "rounded-full px-2.5 py-0.5 text-xs font-medium " +
            STATUS_BADGE_CLASSES[row.status]
          }
        >
          {row.status}
        </span>
      </div>
    </li>
  );
}