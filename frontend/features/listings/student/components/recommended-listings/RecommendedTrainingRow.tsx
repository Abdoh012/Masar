import Link from "next/link";
import { ArrowRight, Briefcase, GraduationCap } from "lucide-react";

import { Button } from "@/shared/components/ui/button";

import { ModeBadge } from "@/features/listings/shared/components/mode-badge/ModeBadge";
import { PaidBadge } from "@/features/listings/shared/components/paid-badge/PaidBadge";
import { CardMeta } from "@/features/listings/shared/components/listing-card/CardMeta";
import { CARD_ACTION_LABEL } from "@/features/listings/shared/components/listing-card/constants";

import { DURATION_DAYS_SUFFIX, DURATION_DAY_SUFFIX } from "./constants";
import type { RecommendedTraining } from "../../types";

interface RecommendedTrainingRowProps {
  training: RecommendedTraining;
}

// Leaf: one recommended training as a full-width horizontal row — company
// monogram, the training's own title with its badges, the shared
// format/duration/deadline/posted meta, then the call to action.
//
// Deliberately not the shared ListingCard: that is a vertical browse card, and
// forcing a full-width row into its two-column shape is what produced the
// cramped four-across dashboard grid. The badges and meta are still the shared
// definitions, deadline included, so the dashboard and the browse grid can't
// disagree on when a training closes. There is no save toggle here — the
// dashboard is a read-only glance, and saving belongs to the browse grid and
// the detail page.
export function RecommendedTrainingRow({ training }: RecommendedTrainingRowProps) {
  const company = training.companyName ?? "Company";

  return (
    <article className="flex flex-col gap-4 rounded-xl border border-border bg-background p-4 transition-all duration-200 hover:border-primary/30 hover:shadow-lift motion-safe:hover:-translate-y-0.5 sm:flex-row sm:items-center sm:gap-5 sm:p-5">
      {/* Company monogram — the recommendation card carries a logo URL, but the
          listings feature has no shared logo component yet and the Masar seal is
          the platform mark, not the employer's, so the initial stands in. */}
      <span className="flex size-12 shrink-0 items-center justify-center rounded-xl bg-primary-tint text-lg font-semibold text-primary-text">
        {company.charAt(0)}
      </span>

      {/* Title + company + badges + meta */}
      <div className="min-w-0 flex-1">
        <h3 className="truncate text-base font-semibold text-primary-text">
          {training.title}
        </h3>

        <p className="mt-0.5 flex flex-wrap items-center gap-x-2 gap-y-0.5 text-sm text-muted-foreground">
          <span className="flex items-center gap-1.5">
            <Briefcase aria-hidden="true" className="size-3.5 shrink-0" />
            <span className="truncate">{company}</span>
          </span>

          {/* The recommendation card names the student's field of study, which is
              the reason this training surfaced — worth more here than it is in
              the browse grid, where the student is already filtering by it. */}
          {training.specializationName ? (
            <span className="flex items-center gap-1.5">
              <GraduationCap aria-hidden="true" className="size-3.5 shrink-0" />
              <span className="truncate">{training.specializationName}</span>
            </span>
          ) : null}
        </p>

        <div className="mt-3 flex flex-wrap items-center gap-2">
          <ModeBadge mode={training.mode} />
          <PaidBadge
            isPaid={training.isPaid}
            trialDays={training.trialDays ?? undefined}
            price={training.price ?? undefined}
          />
        </div>

        <div className="mt-3">
          <CardMeta
            duration={
              training.durationDays != null
                ? `${training.durationDays} ${
                    training.durationDays === 1
                      ? DURATION_DAY_SUFFIX
                      : DURATION_DAYS_SUFFIX
                  }`
                : undefined
            }
            format={training.format}
            createdAt={training.createdAt}
            deadline={training.applicationDeadline ?? undefined}
          />
        </div>
      </div>

      {/* Call to action — right-aligned at every width, where the save toggle
          used to sit opposite it. */}
      <div className="flex shrink-0 items-center justify-end gap-2 border-t border-border pt-3 sm:border-t-0 sm:pt-0">
        <Button asChild size="sm">
          <Link href={`/listings/${training.id}`} className="group">
            {CARD_ACTION_LABEL}
            <ArrowRight
              aria-hidden="true"
              className="size-4 transition-transform group-hover:translate-x-0.5"
            />
          </Link>
        </Button>
      </div>
    </article>
  );
}
