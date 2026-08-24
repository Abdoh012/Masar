import Link from "next/link";

import { Loader2 } from "lucide-react";

import { Button } from "@/shared/components/ui/button";

import type { TrainingApplicationStep } from "../../types";
import { NAVIGATION_LABELS } from "./constants";

interface StepNavigationProps {
  step: TrainingApplicationStep;
  isSubmitting?: boolean;
  onBack?: () => void;
  backHref?: string;
}

// StepNavigation: the wizard's Back / Continue / Submit row (structure rules
// §17 — the submit control is its own leaf, distinct from the fields). Back on
// step 1 returns to the listing via backHref; later steps go back in-place
// (values are preserved by the orchestrator). Continue/Submit is a type=submit,
// so the native `required` validation gates exactly the rendered step before
// advancing; step 3 shows the simulated loading state while submitting.
export function StepNavigation({
  step,
  isSubmitting = false,
  onBack,
  backHref,
}: StepNavigationProps) {
  const isLastStep = step === 3;

  return (
    <div className="flex flex-col-reverse gap-3 border-t border-border pt-6 sm:flex-row sm:items-center sm:justify-between">
      {step === 1 && backHref ? (
        <Button asChild variant="outline">
          <Link href={backHref}>{NAVIGATION_LABELS.backToListing}</Link>
        </Button>
      ) : (
        <Button type="button" variant="outline" onClick={onBack}>
          {NAVIGATION_LABELS.back}
        </Button>
      )}

      <Button type="submit" disabled={isSubmitting} className="w-full cursor-pointer sm:w-auto">
        {isSubmitting ? (
          <>
            <Loader2 className="size-4 animate-spin" />
            {NAVIGATION_LABELS.submitting}
          </>
        ) : isLastStep ? (
          NAVIGATION_LABELS.submit
        ) : (
          NAVIGATION_LABELS.continue
        )}
      </Button>
    </div>
  );
}