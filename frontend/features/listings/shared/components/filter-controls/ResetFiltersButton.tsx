import { RotateCcw } from "lucide-react";

interface ResetFiltersButtonProps {
  onClick: () => void;
  label: string;
}

// ResetFiltersButton: the shared "reset filters" text button with the
// circular-arrow icon, used by the student browse filter bar and the admin
// moderation filters when an active filter is set. Pure leaf — onClick and
// label come from the parent.
export function ResetFiltersButton({ onClick, label }: ResetFiltersButtonProps) {
  return (
    <button
      type="button"
      onClick={onClick}
      className="inline-flex cursor-pointer items-center gap-1.5 text-xs font-medium text-primary-text transition-colors hover:underline"
    >
      <RotateCcw aria-hidden="true" className="size-3.5" />
      {label}
    </button>
  );
}