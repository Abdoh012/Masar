import Link from "next/link";
import { ArrowRight, Briefcase, GraduationCap, MapPin } from "lucide-react";

import { cn } from "@/shared/lib/utils";
import type { Listing } from "@/features/listings/shared/types";

export interface ListingCardProps extends Listing {
  className?: string;
}

// Shared listing card — reused by Recommended Trainings and the future
// browse-listings page. Pure leaf: narrow props, no fetching, no state.
export function ListingCard({
  id,
  title,
  companyName,
  field,
  location,
  mode,
  free,
  className,
}: ListingCardProps) {
  return (
    <article
      className={cn(
        "flex h-full flex-col gap-3 rounded-2xl border border-border bg-card p-5 shadow-card",
        className,
      )}
    >
      <div className="flex items-center justify-between gap-2">
        <span className="rounded-full bg-secondary-tint px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide text-secondary-text">
          {mode}
        </span>
        {free ? (
          <span className="rounded-full bg-primary-tint px-2.5 py-1 text-[11px] font-semibold text-primary-text">
            Free
          </span>
        ) : null}
      </div>

      <h3 className="line-clamp-2 text-base font-semibold text-primary-text">{title}</h3>

      <p className="flex items-center gap-1.5 text-sm text-muted-foreground">
        <Briefcase aria-hidden="true" className="size-4 shrink-0" />
        <span className="truncate">{companyName}</span>
      </p>

      <p className="flex items-center gap-1.5 text-xs text-muted-foreground">
        <GraduationCap aria-hidden="true" className="size-3.5 shrink-0" />
        <span className="truncate">{field}</span>
      </p>

      {location ? (
        <p className="flex items-center gap-1.5 text-xs text-muted-foreground">
          <MapPin aria-hidden="true" className="size-3.5 shrink-0" />
          <span className="truncate">{location}</span>
        </p>
      ) : null}

      <Link
        href={`/listings/${id}`}
        className="group mt-auto inline-flex items-center gap-1.5 pt-2 text-sm font-medium text-primary transition-colors hover:text-primary/80 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
      >
        View training
        <ArrowRight aria-hidden="true" className="size-4 transition-transform group-hover:translate-x-0.5" />
      </Link>
    </article>
  );
}