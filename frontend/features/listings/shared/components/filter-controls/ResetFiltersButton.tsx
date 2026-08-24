import { Button } from "@/shared/components/ui/button";
import { RotateCcw } from "lucide-react";

interface ResetFiltersButtonProps {
  label: string;
  onClick: () => void;
}

// ResetFiltersButton: the shared "reset filters" control (AGENTS.md). A plain
// outlined button that calls back to the owning container to clear its filter
// state. Pure leaf — no state of its own.
export function ResetFiltersButton({ label, onClick }: ResetFiltersButtonProps) {
  return (
    <Button
      type="button"
      onClick={onClick}
      variant="outline"
      className="cursor-pointer font-medium"
    >
      <RotateCcw className="size-4" />
      {label}
    </Button>
  );
}