import type { LucideIcon } from "lucide-react";

interface ContactFieldRowProps {
  icon: LucideIcon;
  label: string;
  value: string | null;
  className?: string;
}

export function ContactFieldRow({
  icon: Icon,
  label,
  value,
  className,
}: ContactFieldRowProps) {
  return (
    <div
      className={[
        "flex items-start gap-3 rounded-lg border border-border bg-card p-3",
        className,
      ]
        .filter(Boolean)
        .join(" ")}
    >
      <span className="grid size-9 shrink-0 place-items-center rounded-full bg-primary-tint text-primary-text mt-0.5">
        <Icon className="size-4" />
      </span>

      <div className="min-w-0 flex-1">
        <p className="truncate text-sm font-medium text-foreground">{label}</p>
        <p className="truncate text-xs text-muted-foreground">{value ?? "—"}</p>
      </div>
    </div>
  );
}
