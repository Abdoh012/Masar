import Link from "next/link";
import { ArrowRight } from "lucide-react";

import { DashboardSection } from "@/shared/components/dashboard-section/DashboardSection";
import { DashboardSectionHeading } from "@/shared/components/dashboard-section/DashboardSectionHeading";

import { ACTIVE_TRAINING, TRAINING_MODE_LABELS } from "./constants";
import { NoActiveTraining } from "./NoActiveTraining";
import { TrialCountdown } from "../../../shared/components/trial-countdown/TrialCountdown";
import type { ActiveApplication } from "../../types";
import ActiveTrainingCard from "./ActiveTrainingCard";

// ActiveTraining: orchestrator — shows the trial /normal /empty presentation.
export function ActiveTraining() {
  const active: ActiveApplication | null = ACTIVE_TRAINING;
  const isTrial = active?.mode === "paid_trial";

  return (
    <DashboardSection elevation="raised">
      <DashboardSectionHeading
        title="Active training"
        action={
          active ? (
            <span className="shrink-0 rounded-full bg-secondary-tint px-3 py-1 text-xs font-semibold uppercase tracking-wide text-secondary-text">
              {TRAINING_MODE_LABELS[active.mode]}
            </span>
          ) : null
        }
      />

      {/* Active training card */}
      {active ? (
        <div className="mt-5 flex flex-1 flex-col">
          <ActiveTrainingCard
            company={active.company}
            listingTitle={active.listingTitle}
            daysRemaining={isTrial ? active.trialDaysRemaining : undefined}
            totalDays={isTrial ? active.trialDays : undefined}
          />

          {/* Start date */}
          <p className="mt-4 font-mono text-xs text-muted-foreground">
            Started {active.startedOn}
          </p>

          {/* Trial countdown */}
          {isTrial &&
          active.trialDaysRemaining != null &&
          active.trialDays != null ? (
            <TrialCountdown
              daysRemaining={active.trialDaysRemaining}
              totalDays={active.trialDays}
            />
          ) : null}

          <Link
            href="/applications"
            className="group mt-auto inline-flex items-center gap-1.5 pt-5 text-sm font-semibold text-primary transition-colors hover:text-primary/80 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
          >
            View application
            <ArrowRight
              aria-hidden="true"
              className="size-4 transition-transform group-hover:translate-x-0.5"
            />
          </Link>
        </div>
      ) : (
        <NoActiveTraining />
      )}
    </DashboardSection>
  );
}
