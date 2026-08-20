import { Check } from "lucide-react";

// SuccessVisual: the circular "updated" icon badge that opens the
// password-updated card. Uses the success status tint (success-bg/fg) — never
// sage, which is reserved for the hire-opportunity signal.
export function SuccessVisual() {
  return (
    <span className="grid size-14 place-items-center rounded-full bg-success-bg text-success-fg">
      <Check className="size-7" strokeWidth={2} />
    </span>
  );
}
