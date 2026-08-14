import Link from "next/link";
import { notFound } from "next/navigation";

import { Briefcase, Building2, CalendarDays, GraduationCap } from "lucide-react";

import { ModeBadge } from "../../../shared/components/mode-badge/ModeBadge";
import { PaidBadge } from "../../../shared/components/paid-badge/PaidBadge";
import { Button } from "@/shared/components/ui/button";

import { ApplyCta } from "./ApplyCta";
import {
  DETAIL_COPY,
  MOCK_APPLIED_LISTING_IDS,
  MOCK_DETAIL_LISTINGS,
} from "./constants";

interface ListingDetailProps {
  id: string;
}

// Student listing detail orchestrator (FR-015). Server component: reads the
// mock listing by id from constants and renders the full listing with the
// shared badges (long descriptions/specializations must not break layout —
// wrapping + truncation handled here). Composes ApplyCta or the
// already-applied status (FR-017). UI-only; unknown ids hit the route
// shell's not-found sibling via notFound().

export function ListingDetail({ id }: ListingDetailProps) {
  const listing = MOCK_DETAIL_LISTINGS[id];

  if (!listing) {
    notFound();
  }

  const alreadyApplied = MOCK_APPLIED_LISTING_IDS.includes(id);

  return (
    <article className="space-y-8">
      <div className="space-y-4 rounded-2xl border border-border bg-card p-6">
        <div className="flex flex-wrap items-center gap-2">
          <ModeBadge mode={listing.mode} />
          <PaidBadge isPaid={listing.isPaid} trialDays={listing.trialDays} />
        </div>

        <h2 className="font-sans text-2xl font-semibold text-foreground">
          {listing.specialization}
        </h2>

        <dl className="grid gap-3 text-sm text-muted-foreground sm:grid-cols-2">
          <div className="flex items-center gap-2">
            <Building2 aria-hidden="true" className="size-4 shrink-0 text-primary-text" />
            <dt className="sr-only">Company</dt>
            <dd>{listing.companyName}</dd>
          </div>
          <div className="flex items-center gap-2">
            <Briefcase aria-hidden="true" className="size-4 shrink-0 text-primary-text" />
            <dt className="sr-only">Field</dt>
            <dd>{listing.field}</dd>
          </div>
          <div className="flex items-center gap-2">
            <GraduationCap aria-hidden="true" className="size-4 shrink-0 text-primary-text" />
            <dt className="sr-only">Format</dt>
            <dd className="capitalize">{listing.format.replace("_", " ")}</dd>
          </div>
          <div className="flex items-center gap-2">
            <CalendarDays aria-hidden="true" className="size-4 shrink-0 text-primary-text" />
            <dt className="sr-only">Posted</dt>
            <dd>Posted {listing.createdAt}</dd>
          </div>
        </dl>

        <p className="whitespace-pre-line text-sm leading-relaxed text-foreground">
          {listing.description}
        </p>
      </div>

      <div className="rounded-2xl border border-border bg-card p-6">
        <ApplyCta listingId={listing.id} appliedByDefault={alreadyApplied} />
      </div>

      <Button asChild variant="outline">
        <Link href="/listings">{DETAIL_COPY.backToBrowse}</Link>
      </Button>
    </article>
  );
}
