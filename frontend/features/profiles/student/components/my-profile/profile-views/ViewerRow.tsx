import { formatRelativeTime } from "../../../lib/format";
import { VIEWER_TINTS } from "./constants";
import type { ProfileViewer } from "../../../types";

interface ViewerRowProps {
  viewer: ProfileViewer;
}

// ViewerRow: one person in the profile-views list — initials avatar, name, the
// company or role they came from, and how long ago they looked. The tint is
// picked from the viewer's id so the same person keeps the same colour between
// renders without the list having to pass presentation down.
export function ViewerRow({ viewer }: ViewerRowProps) {
  const { id, name, role, initials, viewedAt } = viewer;
  const tint = VIEWER_TINTS[id % VIEWER_TINTS.length];

  return (
    <li className="flex items-center gap-3 rounded-lg border border-border bg-card p-3">
      <span
        className={`grid size-9 shrink-0 place-items-center rounded-full text-xs font-semibold ${tint}`}
      >
        {initials}
      </span>

      <div className="min-w-0 flex-1">
        <p className="truncate text-sm font-medium text-foreground">{name}</p>
        <p className="truncate text-xs text-muted-foreground">{role}</p>
      </div>

      <time
        dateTime={viewedAt}
        className="shrink-0 text-xs whitespace-nowrap text-muted-foreground"
      >
        {formatRelativeTime(viewedAt)}
      </time>
    </li>
  );
}
