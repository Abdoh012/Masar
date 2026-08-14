import { Hourglass } from "lucide-react";

// PendingStatusVisual: the circular "under review" icon badge that opens the
// pending-approval card. Amber warning tint reads as awaiting review — not
// an error (red) nor a confirmed success (sage). Mirrors the root states'
// circular icon treatment (bg-*-bg / text-*-fg).
export function PendingStatusVisual() {
  return (
    <span className="grid size-14 place-items-center rounded-full bg-warning-bg text-warning-fg">
      <Hourglass className="size-7" strokeWidth={2} />
    </span>
  );
}
