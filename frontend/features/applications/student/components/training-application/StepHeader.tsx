import type { TrainingApplicationStep } from "../../types";
import { STEP_HEADERS } from "./constants";

interface StepHeaderProps {
  step: TrainingApplicationStep;
}

// StepHeader: the current step's title + description band inside the wizard
// card. Copy comes from STEP_HEADERS so the header can never drift from the
// progress rail. Pure leaf.
export function StepHeader({ step }: StepHeaderProps) {
  const { title, description } = STEP_HEADERS[step];

  return (
    <div className="space-y-1.5">
      <p className="font-mono text-xs font-semibold uppercase tracking-[0.2em] text-secondary-text">
        Step {step} of {Object.keys(STEP_HEADERS).length}
      </p>
      <h2 className="font-sans text-xl font-semibold text-primary-text">{title}</h2>
      <p className="text-sm leading-relaxed text-muted-foreground">{description}</p>
    </div>
  );
}