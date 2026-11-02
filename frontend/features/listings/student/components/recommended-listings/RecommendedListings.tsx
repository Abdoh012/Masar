import Link from "next/link";
import { Sparkles } from "lucide-react";

import { DashboardSection } from "@/shared/components/dashboard-section/DashboardSection";
import { DashboardSectionHeading } from "@/shared/components/dashboard-section/DashboardSectionHeading";

import { RECOMMENDED_LABELS, RECOMMENDED_LIMIT } from "./constants";
import { NoRecommendations } from "./NoRecommendations";
import { RecommendedTrainingRow } from "./RecommendedTrainingRow";
import type { RecommendedTraining } from "../../types";

interface RecommendedListingsProps {
  trainings: RecommendedTraining[];
}

// RecommendedListings: the trainings the backend matched to this student, as
// full-width rows, or an empty state when it matched none.
//
// The list arrives from the dashboard orchestrator, which reads it off
// GET /students/dashboard — the section fetches nothing for itself. The
// previous build drew this grid from a static mock pool, which meant the
// "recommendations" were identical for every student on every visit.
export function RecommendedListings({ trainings }: RecommendedListingsProps) {
  return (
    <DashboardSection>
      <DashboardSectionHeading
        title={RECOMMENDED_LABELS.title}
        icon={
          <Sparkles aria-hidden="true" className="size-5 text-secondary-text" />
        }
        action={
          trainings.length > 0 ? (
            <Link
              href="/listings"
              className="inline-flex shrink-0 items-center gap-1.5 text-sm font-medium text-primary transition-colors hover:text-primary/80 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
            >
              {RECOMMENDED_LABELS.viewAll}
            </Link>
          ) : null
        }
      />

      {trainings.length === 0 ? (
        <NoRecommendations />
      ) : (
        <div className="mt-5 flex flex-col gap-3">
          {trainings.slice(0, RECOMMENDED_LIMIT).map((training) => (
            <RecommendedTrainingRow key={training.id} training={training} />
          ))}
        </div>
      )}
    </DashboardSection>
  );
}
