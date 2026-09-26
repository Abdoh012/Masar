import Link from "next/link";
import { ArrowRight, Briefcase } from "lucide-react";

import { Button } from "@/shared/components/ui/button";

import { ModeBadge } from "@/features/listings/shared/components/mode-badge/ModeBadge";
import { PaidBadge } from "@/features/listings/shared/components/paid-badge/PaidBadge";
import { CardMeta } from "@/features/listings/shared/components/listing-card/CardMeta";
import { CARD_ACTION_LABEL } from "@/features/listings/shared/components/listing-card/constants";
import type { ListingCardData } from "@/features/listings/shared/types";

interface RecommendedTrainingRowProps {
  listing: ListingCardData;
}

// Leaf: one recommended training as a full-width horizontal row — company
// monogram, the title with its badges, the shared format/deadline/posted meta,
// then the call to action. Deliberately not the shared ListingCard: that is a
// vertical browse card, and forcing a full-width row into its two-column shape
// is what produced the cramped four-across dashboard grid. The badges and meta
// are still the shared definitions, deadline included, so the dashboard and the
// browse grid can't disagree on when a listing closes. There is no save toggle
// here — the dashboard is a read-only glance, and saving belongs to the browse
// grid and the detail page.
export function RecommendedTrainingRow({ listing }: RecommendedTrainingRowProps) {
  return (
    <article className="flex flex-col gap-4 rounded-xl border border-border bg-background p-4 transition-all duration-200 hover:border-primary/30 hover:shadow-lift motion-safe:hover:-translate-y-0.5 sm:flex-row sm:items-center sm:gap-5 sm:p-5">
      {/* Company monogram — the real listing carries no per-company logo yet,
          and the Masar seal is the platform mark, not the employer's. */}
      <span className="flex size-12 shrink-0 items-center justify-center rounded-xl bg-primary-tint text-lg font-semibold text-primary-text">
        {listing.companyName.charAt(0)}
      </span>

      {/* Title + company + badges + meta */}
      <div className="min-w-0 flex-1">
        <h3 className="truncate text-base font-semibold text-primary-text">
          {listing.specialization}
        </h3>

        <p className="mt-0.5 flex items-center gap-1.5 text-sm text-muted-foreground">
          <Briefcase aria-hidden="true" className="size-3.5 shrink-0" />
          <span className="truncate">{listing.companyName}</span>
        </p>

        <div className="mt-3 flex flex-wrap items-center gap-2">
          <ModeBadge mode={listing.mode} />
          <PaidBadge
            isPaid={listing.isPaid}
            trialDays={listing.trialDays}
            price={listing.price}
            currency={listing.currency}
          />
        </div>

        <div className="mt-3">
          <CardMeta
            format={listing.format}
            createdAt={listing.createdAt}
            deadline={listing.applicationDeadline}
          />
        </div>
      </div>

      {/* Call to action — right-aligned at every width, where the save toggle
          used to sit opposite it. */}
      <div className="flex shrink-0 items-center justify-end gap-2 border-t border-border pt-3 sm:border-t-0 sm:pt-0">
        <Button asChild size="sm">
          <Link href={`/listings/${listing.id}`} className="group">
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
