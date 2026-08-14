import Link from "next/link";
import { ArrowRight, Briefcase, GraduationCap } from "lucide-react";

import { cn } from "@/shared/lib/utils";
import type { ListingCardData } from "@/features/listings/shared/types";
import { ModeBadge } from "@/features/listings/shared/components/mode-badge/ModeBadge";
import { PaidBadge } from "@/features/listings/shared/components/paid-badge/PaidBadge";
import { CARD_ACTION_LABEL } from "./constants";

// ListingCard: the one shared listing card, reused by student browse and the
// dashboard's Recommended Listings (FR-022 — no second card definition).
// Consumes ListingCardData (UI-only display fields). Pure leaf: narrow props,
// no fetching, no state.
export function ListingCard({ ...listing }: ListingCardData & { className?: string }) {
  const { id, specialization, companyName, field, mode, isPaid, trialDays, className } = listing;

  return (
    <article
      className={cn(
        "flex h-full flex-col gap-3 rounded-2xl border border-border bg-card p-5 shadow-card",
        className,
      )}
    >
      <div className="flex items-center justify-between gap-2">
        <ModeBadge mode={mode} />
        <PaidBadge isPaid={isPaid} trialDays={trialDays} />
      </div>

      <h3 className="line-clamp-2 text-base font-semibold text-primary-text">{specialization}</h3>

      <p className="flex items-center gap-1.5 text-sm text-muted-foreground">
        <Briefcase aria-hidden="true" className="size-4 shrink-0" />
        <span className="truncate">{companyName}</span>
      </p>

      <p className="flex items-center gap-1.5 text-xs text-muted-foreground">
        <GraduationCap aria-hidden="true" className="size-3.5 shrink-0" />
        <span className="truncate">{field}</span>
      </p>

      <Link
        href={`/listings/${id}`}
        className="group mt-auto inline-flex items-center gap-1.5 pt-2 text-sm font-medium text-primary transition-colors hover:text-primary/80 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
      >
        {CARD_ACTION_LABEL}
        <ArrowRight aria-hidden="true" className="size-4 transition-transform group-hover:translate-x-0.5" />
      </Link>
    </article>
  );
}