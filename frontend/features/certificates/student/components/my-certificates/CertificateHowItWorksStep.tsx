import { CheckCircle2, Download, Send } from "lucide-react";

import { HOW_IT_WORKS_STEPS } from "./constants";

const STEP_ICONS = {
  check: CheckCircle2,
  send: Send,
  download: Download,
} as const;

export function CertificateHowItWorksStep({
  step,
}: {
  step: (typeof HOW_IT_WORKS_STEPS)[number];
}) {
  const Icon = STEP_ICONS[step.icon];

  return (
    <div className="flex flex-col items-start gap-3">
      <span className="flex size-10 shrink-0 items-center justify-center rounded-full bg-primary-tint text-primary-text">
        <Icon className="size-4" />
      </span>
      <div>
        <p className="text-sm font-semibold text-primary-text">{step.title}</p>
        <p className="mt-1 text-xs leading-relaxed text-muted-foreground">{step.body}</p>
      </div>
    </div>
  );
}
