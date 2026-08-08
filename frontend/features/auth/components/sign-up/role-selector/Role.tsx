import { RoleType } from "@/features/auth/lib/constants";
import { LucideIcon } from "lucide-react";

interface RoleProps {
  value: RoleType;
  activeRole: RoleType;
  onChange: (role: RoleType) => void;
  Icon: LucideIcon;
  label: string;
  description: string;
}

export default function Role({
  value,
  onChange,
  activeRole,
  Icon,
  label,
  description,
}: RoleProps) {
  const selected = activeRole === value;

  return (
    <button
      key={value}
      type="button"
      onClick={() => onChange(value)}
      className={`flex flex-col items-start gap-1.5 rounded-xl border px-4 py-3.5 text-left transition-colors cursor-pointer
        ${
          selected
            ? "border-primary bg-primary text-primary-foreground"
            : "border-border bg-card text-foreground hover:border-primary/40"
        }`}
    >
      {/* Icon + Label */}
      <div className="flex items-center gap-1">
        <Icon
          className={`"h-4 w-4" ${selected ? "text-secondary-500" : "text-muted-foreground"}`}
        />
        <span className="text-sm font-semibold">{label}</span>
      </div>

      {/* Description */}
      <p
        className={`text-xs leading-snug ${selected ? "text-primary-foreground/70" : "text-muted-foreground"}`}
      >
        {description}
      </p>
    </button>
  );
}
