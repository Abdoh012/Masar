import { STATUS_BADGE_CLASSES, type ApplicationStatus } from "./constants";

export interface StatusCountBadgeProps {
  label: string;
  count: number;
  status: ApplicationStatus;
}

// Leaf: one status count tile. Token mapping comes from constants (STATUS_BADGE_CLASSES).
export function StatusCountBadge({ label, count, status }: StatusCountBadgeProps) {
  return (
    <div
      className={
        "flex flex-col gap-0.5 rounded-xl px-3 py-2.5 " + STATUS_BADGE_CLASSES[status]
      }
    >
      <span className="text-2xl font-semibold leading-none">{count}</span>
      <span className="text-xs font-medium opacity-80">{label}</span>
    </div>
  );
}