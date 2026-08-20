import { Check } from "lucide-react";

import { cn } from "@/shared/lib/utils";

import type { TrainingApplicationStep } from "../../types";
import { STEPS } from "./constants";

interface ProgressIndicatorProps {
  currentStep: TrainingApplicationStep;
}

// ProgressIndicator: the wizard's step progress rail (completed → active →
// upcoming). Display-only — backward navigation is handled by the form's Back
// button, so the rail never takes focus. Labels are hidden below sm so the rail
// never overflows on small screens; the current step's title is still shown
// right below in the StepHeader.
export function ProgressIndicator({ currentStep }: ProgressIndicatorProps) {
  return (
    <ol className="flex items-center">
      {STEPS.map((step, index) => {
        const stepNumber = (index + 1) as TrainingApplicationStep;
        const isCompleted = stepNumber < currentStep;
        const isActive = stepNumber === currentStep;
        const isLast = index === STEPS.length - 1;

        return (
          <li key={step.label} className={cn("flex items-center", !isLast && "flex-1")}>
            {index > 0 ? (
              <span
                aria-hidden="true"
                className={cn(
                  "mx-2 h-px flex-1 sm:mx-3",
                  stepNumber <= currentStep ? "bg-primary" : "bg-border",
                )}
              />
            ) : null}

            <span className="flex flex-col items-center gap-1.5">
              <span
                aria-current={isActive ? "step" : undefined}
                className={cn(
                  "grid size-7 place-items-center rounded-full text-sm font-semibold",
                  isCompleted || isActive
                    ? "bg-primary text-primary-foreground"
                    : "border border-border bg-card text-muted-foreground",
                )}
              >
                {isCompleted ? <Check className="size-4" /> : stepNumber}
              </span>

              <span
                className={cn(
                  "hidden max-w-[8rem] text-center text-xs font-medium sm:block",
                  isActive || isCompleted ? "text-primary-text" : "text-muted-foreground",
                )}
              >
                {step.label}
              </span>
            </span>
          </li>
        );
      })}
    </ol>
  );
}