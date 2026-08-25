import { Check } from "lucide-react";

import { cn } from "@/shared/lib/utils";

import type { TrainingApplicationStep } from "../../types";
<<<<<<< HEAD
import { STEPS } from "./constants";
=======
import { PROGRESS_RAIL_HEADING, STEPS, STEP_HEADERS } from "./constants";
>>>>>>> dev

interface ProgressIndicatorProps {
  currentStep: TrainingApplicationStep;
}

// ProgressIndicator: the wizard's step progress rail (completed → active →
<<<<<<< HEAD
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
=======
// upcoming), rendered from the same STEPS source in two orientations — a
// compact horizontal strip above the form on small screens, and a vertical
// "Application Progress" timeline on the form's left from md up (the
// wrapper's right border ties it to the form pane). Display-only — backward
// navigation is handled by the form's Back button, so neither rail ever takes
// focus.
export function ProgressIndicator({ currentStep }: ProgressIndicatorProps) {
  return (
    <div className="shrink-0 md:w-44 md:rounded-lg md:border md:border-border md:bg-background/60 md:p-5 lg:w-56 xl:w-60">
      {/* Compact horizontal strip (mobile): labels hidden below sm so it
          never overflows; the current step's title still shows below in the
          StepHeader. */}
      <ol className="flex items-center md:hidden">
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

      {/* Vertical rail (tablet/desktop): a lightweight progress timeline —
          "Application Progress" heading, then circle-left / text-right rows
          joined by a thin dashed connector aligned with the circle centers.
          Descriptions reuse the existing STEP_HEADERS copy so the rail can
          never drift from the StepHeader. */}
      <div className="hidden md:block">
        <p className="mb-5 text-sm font-semibold text-primary-text">
          {PROGRESS_RAIL_HEADING}
        </p>

        <ol className="flex flex-col">
          {STEPS.map((step, index) => {
            const stepNumber = (index + 1) as TrainingApplicationStep;
            const isCompleted = stepNumber < currentStep;
            const isActive = stepNumber === currentStep;
            const isLast = index === STEPS.length - 1;

            return (
              <li key={step.label} className="flex gap-3">
                <div className="flex flex-col items-center self-stretch">
                  <span
                    aria-current={isActive ? "step" : undefined}
                    className={cn(
                      "grid size-7 shrink-0 place-items-center rounded-full text-sm font-semibold",
                      isCompleted || isActive
                        ? "bg-primary text-primary-foreground"
                        : "border border-border bg-card text-muted-foreground",
                    )}
                  >
                    {isCompleted ? <Check className="size-4" /> : stepNumber}
                  </span>

                  {!isLast ? (
                    <span
                      aria-hidden="true"
                      className="mb-1.5 mt-1.5 w-px flex-1 border-l border-dashed border-border"
                    />
                  ) : null}
                </div>

                <div className={cn("pt-1", !isLast && "pb-6")}>
                  <p
                    className={cn(
                      "text-sm leading-snug text-primary-text",
                      isActive ? "font-semibold" : "font-medium",
                    )}
                  >
                    {step.label}
                  </p>
                  <p className="mt-1 text-xs leading-snug text-muted-foreground">
                    {STEP_HEADERS[stepNumber].description}
                  </p>
                </div>
              </li>
            );
          })}
        </ol>
      </div>
    </div>
>>>>>>> dev
  );
}