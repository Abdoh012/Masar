import { cn } from "@/shared/lib/utils";

import { CATEGORY_STYLES, DEFAULT_CATEGORY_STYLE } from "./constants";

interface CategoryPillProps {
  field: string;
  className?: string;
}

export function CategoryPill({ field, className }: CategoryPillProps) {
  const style = CATEGORY_STYLES[field] ?? DEFAULT_CATEGORY_STYLE;

  return (
    <span
      className={`rounded-full px-2.5 py-1 text-sm font-semibold  w-fit ${style.pill} ${className}`}
    >
      {field}
    </span>
  );
}
