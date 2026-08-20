import { cn } from "@/shared/lib/utils";

import { CATEGORY_STYLES, DEFAULT_CATEGORY_STYLE } from "./constants";

interface CategoryPillProps {
  field: string;
  className?: string;
}

// CategoryPill: the uppercase category label pill on the shared ListingCard,
// colored per the field via CATEGORY_STYLES. Pure presentational leaf.
export function CategoryPill({ field, className }: CategoryPillProps) {
  const style = CATEGORY_STYLES[field] ?? DEFAULT_CATEGORY_STYLE;

  return (
    <span
      className={cn(
        "rounded-full px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide",
        style.pill,
        className,
      )}
    >
      {field}
    </span>
  );
}