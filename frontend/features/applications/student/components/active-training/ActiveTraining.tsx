import Link from "next/link";
import { ArrowRight } from "lucide-react";

import Motion from "@/shared/components/animation/Motion";
import { fadeInUp } from "@/shared/lib/animations";
import {
  ACTIVE_TRAINING,
  TRAINING_MODE_LABELS,
  type ActiveApplication,
} from "./constants";
import { NoActiveTraining } from "./NoActiveTraining";
import { TrialCountdown } from "./TrialCountdown";

// ActiveTraining: orchestrator — shows the trial /normal /empty presentation.
export function ActiveTraining() {
  const active: ActiveApplication | null = ACTIVE_TRAINING;
  const isTrial = active?.mode === "paid_trial";

  return (
    <Motion
      variants={fadeInUp}
      initial="hidden"
      whileInView="visible"
      viewport={{ once: true, margin: "-40px" }}
      transition={{ duration: 0.24, ease: "easeOut" }}
      className="flex h-full flex-col rounded-2xl border border-border bg-card p-5 shadow-card"
    >
      <div className="flex items-center justify-between gap-2">
        <h2 className="text-base font-semibold text-primary-text">Active training</h2>
        {active ? (
          <span className="shrink-0 rounded-full bg-secondary-tint px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide text-secondary-text">
            {TRAINING_MODE_LABELS[active.mode]}
          </span>
        ) : null}
      </div>

      {active ? (
        <div className="mt-4 flex flex-1 flex-col">
          <div className="flex items-center gap-3 rounded-xl bg-background p-3">
            <span
              aria-hidden="true"
              className="flex size-10 shrink-0 items-center justify-center rounded-full bg-primary-tint text-sm font-semibold text-primary-text"
            >
              {active.company.charAt(0)}
            </span>
            <div className="min-w-0">
              <p className="text-[11px] font-semibold uppercase tracking-[0.08em] text-muted-foreground">
                {active.company}
              </p>
              <p className="truncate text-sm font-medium text-foreground">
                {active.listingTitle}
              </p>
            </div>
          </div>

          <p className="mt-3 font-mono text-xs text-muted-foreground">
            Started {active.startedOn}
          </p>

          {isTrial && active.trialDaysRemaining ? (
            <TrialCountdown daysRemaining={active.trialDaysRemaining} />
          ) : null}

          <Link
            href="/applications"
            className="group mt-auto inline-flex items-center gap-1.5 pt-4 text-sm font-medium text-primary transition-colors hover:text-primary/80 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
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
    </Motion>
  );
}