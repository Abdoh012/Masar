import Link from "next/link";
import { ArrowRight } from "lucide-react";

import { DashboardSection } from "@/shared/components/dashboard-section/DashboardSection";
import { DashboardSectionHeading } from "@/shared/components/dashboard-section/DashboardSectionHeading";

import { calculateTrainingProgress } from "../../lib/training-progress";
import { formatApplicationDate } from "../my-applications/constants";
import type { ActiveTrainingView } from "../../types";
import {
  DAYS_REMAINING_LABEL,
  TRAINING_MODE_LABELS,
  TRAINING_PROGRESS_COPY,
} from "./constants";
import { ActiveTrainingCard } from "./ActiveTrainingCard";
import { NoActiveTraining } from "./NoActiveTraining";

// ActiveTraining: orchestrator — the latest accepted training's run-down, or the
// empty state. The progress bar is derived here, once, from the training's own
// start/end dates (pure util, no client state), and handed to the card as a
// plain number. `active` arrives from the dashboard orchestrator, which reads
// it off GET /students/dashboard.
export function ActiveTraining({ active }: { active: ActiveTrainingView | null }) {
  if (!active) {
    return (
      <DashboardSection elevation="raised">
        <DashboardSectionHeading title="Active training" />
        <NoActiveTraining />
      </DashboardSection>
    );
  }

  const progress = calculateTrainingProgress({
    startsAt: active.startsAt,
    endsAt: active.endsAt,
  });
  const copy = TRAINING_PROGRESS_COPY[progress.state];
  const startedOn = active.startsAt ? formatApplicationDate(active.startsAt) : "";

  return (
    <DashboardSection elevation="raised">
      <DashboardSectionHeading
        title="Active training"
        action={
          <span className="shrink-0 rounded-full bg-secondary-tint px-3 py-1 text-xs font-semibold uppercase tracking-wide text-secondary-text">
            {TRAINING_MODE_LABELS[active.deliveryMode]}
          </span>
        }
      />

      <div className="mt-5 flex flex-1 flex-col">
        <ActiveTrainingCard
          company={active.company}
          listingTitle={active.listingTitle}
          percent={progress.percent}
        />

        {/* Run state + the backend's own calendar-day countdown. The bar above is
            the percentage; this is the day count, so the two never compete. */}
        <div className="mt-4 flex flex-wrap items-center justify-between gap-2">
          <p className="font-mono text-xs text-muted-foreground">
            {startedOn ? `Started ${startedOn}` : copy.label}
          </p>

          {active.remainingDays != null ? (
            <span className="rounded-full bg-primary-tint px-3 py-1 text-xs font-semibold text-primary-text">
              {DAYS_REMAINING_LABEL(active.remainingDays)}
            </span>
          ) : null}
        </div>

        <p className="mt-2 text-xs text-muted-foreground">{copy.subline}</p>

        <Link
          href="/applications"
          className="group mt-auto inline-flex items-center gap-1.5 pt-5 text-sm font-semibold text-primary transition-colors hover:text-primary/80 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
        >
          View all applications
          <ArrowRight
            aria-hidden="true"
            className="size-4 transition-transform group-hover:translate-x-0.5"
          />
        </Link>
      </div>
    </DashboardSection>
  );
}
