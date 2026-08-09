import Link from "next/link";
import { Sparkles } from "lucide-react";

import Motion from "@/shared/components/animation/Motion";
import { fadeInUp } from "@/shared/lib/animations";
import { ListingCard } from "@/features/listings/shared/components/listing-card/ListingCard";
import { FALLBACK_LISTINGS, RECOMMENDED_LISTINGS } from "./constants";

// RecommendedListings: maps mock listings through the one shared ListingCard.
// Empty field → falls back to the general/newest set (never a blank section).
export function RecommendedListings() {
  const listings =
    RECOMMENDED_LISTINGS.length > 0 ? RECOMMENDED_LISTINGS : FALLBACK_LISTINGS;

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
        {/* Header */}
        <h2 className="flex items-center gap-2 text-base font-semibold text-primary-text">
          <Sparkles aria-hidden="true" className="size-4 text-secondary-text" />
          Recommended trainings
        </h2>

        {/* View all */}
        <Link
          href="/listings"
          className="inline-flex shrink-0 items-center gap-1.5 text-sm font-medium text-primary transition-colors hover:text-primary/80 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
        >
          View all
        </Link>
      </div>

      {/* recommended listings grid */}
      <div className="mt-4 grid flex-1 gap-4 sm:grid-cols-2">
        {listings.map((listing) => (
          <ListingCard key={listing.id} {...listing} />
        ))}
      </div>
    </Motion>
  );
}
