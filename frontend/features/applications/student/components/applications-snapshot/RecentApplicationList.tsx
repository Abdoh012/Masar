import type { RecentApplicationRow } from "../../types";
import { RecentApplicationRow as RecentApplicationRowItem } from "./RecentApplicationRow";

// Leaf: the snapshot's recent-applications list, one row per entry. Kept apart
// from the orchestrator so the tiles and the list are two independent pieces of
// the section rather than one long render.
export function RecentApplicationList({ rows }: { rows: RecentApplicationRow[] }) {
  if (rows.length === 0) return null;

  return (
    <ul className="mt-4">
      {rows.map((row) => (
        <RecentApplicationRowItem key={row.id} row={row} />
      ))}
    </ul>
  );
}
