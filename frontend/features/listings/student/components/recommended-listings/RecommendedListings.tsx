import Link from "next/link";
import { Sparkles } from "lucide-react";

import { DashboardSection } from "@/shared/components/dashboard-section/DashboardSection";
import { DashboardSectionHeading } from "@/shared/components/dashboard-section/DashboardSectionHeading";

import {
  FALLBACK_LISTINGS,
  RECOMMENDED_LABELS,
  RECOMMENDED_LIMIT,
  RECOMMENDED_LISTINGS,
} from "./constants";
import { RecommendedTrainingRow } from "./RecommendedTrainingRow";

// RecommendedListings: the two most relevant trainings as full-width rows.
// Empty field → falls back to the general/newest set (never a blank section).
export function RecommendedListings() {
  const pool =
    RECOMMENDED_LISTINGS.length > 0 ? RECOMMENDED_LISTINGS : FALLBACK_LISTINGS;
  const listings = pool.slice(0, RECOMMENDED_LIMIT);

  return (
    <DashboardSection>
      <DashboardSectionHeading
        title={RECOMMENDED_LABELS.title}
        icon={
          <Sparkles aria-hidden="true" className="size-5 text-secondary-text" />
        }
        action={
          <Link
            href="/listings"
            className="inline-flex shrink-0 items-center gap-1.5 text-sm font-medium text-primary transition-colors hover:text-primary/80 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
          >
            {RECOMMENDED_LABELS.viewAll}
          </Link>
        }
      />

      <div className="mt-5 flex flex-col gap-3">
        {listings.map((listing) => (
          <RecommendedTrainingRow key={listing.id} listing={listing} />
        ))}
      </div>
    </DashboardSection>
  );
}
