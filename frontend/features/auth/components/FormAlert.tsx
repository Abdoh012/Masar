import { CircleAlert } from "lucide-react";

interface FormAlertProps {
  message: string;
}

// FormAlert: top-level error banner for form-level failures (network
// errors, "invalid credentials"). Presentational — rendered by client
// forms when the action returns a non-field message.
export function FormAlert({ message }: FormAlertProps) {
  return (
    <div
      role="alert"
      className="flex items-start gap-2.5 rounded-lg border border-error-500/25 bg-error-bg px-3.5 py-3 text-sm text-error-fg"
    >
      <CircleAlert className="mt-0.5 h-4 w-4 shrink-0" />
      <p className="leading-relaxed">{message}</p>
    </div>
  );
}
