import type { ReactNode } from "react";
import type { LucideIcon } from "lucide-react";

interface DetailMetaRowProps {
  icon: LucideIcon;
  label: string;
  children: ReactNode;
}

// DetailMetaRow: one labeled row of the listing detail `<dl>` (FR-015). Server
// leaf — the sr-only `<dt>` carries the accessible label and the icon is
// decorative, so the four meta rows (company, field, format, posted) share
// this single definition instead of repeating the markup (structure rules §5).
export function DetailMetaRow({ icon: Icon, label, children }: DetailMetaRowProps) {
  return (
    <div className="flex items-center gap-2">
      <Icon aria-hidden="true" className="size-4 shrink-0 text-primary-text" />
      <dt className="sr-only">{label}</dt>
      <dd>{children}</dd>
    </div>
  );
}
