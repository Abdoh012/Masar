import { Button } from "@/shared/components/ui/button";
import { RotateCcw } from "lucide-react";

interface ResetFiltersButtonProps {
  label: string;
}

export function ResetFiltersButton({ label }: ResetFiltersButtonProps) {
  return (
    <Button
      type="button"
      className="cursor-pointer font-medium text-white transition-all duration-300 hover:bg-red-400 bg-red-500 rounded-md h-full"
    >
      {label}
    </Button>
  );
}
