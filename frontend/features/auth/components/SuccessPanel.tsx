import { Check } from "lucide-react";
import type { ReactNode } from "react";

interface SuccessPanelProps {
  title: string;
  message: string;
  children?: ReactNode;
}

// SuccessPanel: confirmation state shown after a form succeeds (e.g.
// "check your inbox", "password updated"). Uses the gold-tinted seal
// circle — the brand's verified/confirmation signal — not sage, which is
// reserved for hire-confirmed only. Presentational; the caller (a client
// form) swaps the form out for this when the action reports success.
export function SuccessPanel({ title, message, children }: SuccessPanelProps) {
  return (
    <div className="flex flex-col items-center gap-3 py-4 text-center">
      <span className="grid size-12 place-items-center rounded-full bg-secondary-tint text-secondary-text">
        <Check className="h-6 w-6" strokeWidth={2.5} />
      </span>
      <h3 className="font-sans text-lg font-semibold text-primary-text">{title}</h3>
      <p className="max-w-sm text-sm leading-relaxed text-muted-foreground">{message}</p>
      {children}
    </div>
  );
}
