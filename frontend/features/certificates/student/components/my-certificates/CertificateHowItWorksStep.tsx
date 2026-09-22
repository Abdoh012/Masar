import { CheckCircle2, Download, Send } from "lucide-react";

import { HOW_IT_WORKS_STEPS } from "./constants";

const STEP_ICONS = {
  check: CheckCircle2,
  send: Send,
  download: Download,
} as const;

// CertificateHowItWorksStep: one step in the certificate flow. The icon circle
// keeps the step's meaning; a solid navy number chip (1/2/3) overlays its
// corner so the order reads even without the rail. Centered on sm+ so the icon
// sits on the orchestrator's dashed connector; stacks cleanly on mobile.
export function CertificateHowItWorksStep({
  step,
  index,
}: {
  step: (typeof HOW_IT_WORKS_STEPS)[number];
  index: number;
}) {
  const Icon = STEP_ICONS[step.icon];

  return (
    <li className="flex flex-col items-center gap-3 text-center">
      <span className="relative flex size-12 shrink-0 items-center justify-center rounded-full bg-primary-tint text-primary-text">
        <Icon className="size-5" />
        <span className="absolute -right-1.5 -top-1.5 flex size-5 items-center justify-center rounded-full bg-primary text-[11px] font-bold text-primary-foreground">
          {index + 1}
        </span>
      </span>
      <div className="min-w-0">
        <p className="text-sm font-semibold text-primary-text">{step.title}</p>
        <p className="mx-auto mt-1 max-w-[24ch] text-xs leading-relaxed text-muted-foreground">
          {step.body}
        </p>
      </div>
    </li>
  );
}