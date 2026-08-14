import Link from "next/link";
import { notFound } from "next/navigation";

import { Briefcase, Building2, CalendarDays, GraduationCap } from "lucide-react";

import { FORMAT_LABELS } from "../../../shared/lib/constants";
import { ModeBadge } from "../../../shared/components/mode-badge/ModeBadge";
import { PaidBadge } from "../../../shared/components/paid-badge/PaidBadge";
import { Button } from "@/shared/components/ui/button";

import { ApplyCta } from "./ApplyCta";
import { DetailMetaRow } from "./DetailMetaRow";
import {
  DETAIL_COPY,
  DETAIL_META,
  MOCK_APPLIED_LISTING_IDS,
  MOCK_DETAIL_LISTINGS,
} from "./constants";

interface ListingDetailContainerProps {
  id: string;
}

// Student listing detail orchestrator (FR-015). Server component: reads the
// mock listing by id from constants and renders the full listing with the
// shared badges (long descriptions/specializations must not break layout —
// wrapping + truncation handled here). Composes ApplyCta or the
// already-applied status (FR-017). UI-only; unknown ids hit the route
// shell's not-found sibling via notFound().

export function ListingDetailContainer({ id }: ListingDetailContainerProps) {
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
          <DetailMetaRow icon={Building2} label={DETAIL_META.company}>
            {listing.companyName}
          </DetailMetaRow>
          <DetailMetaRow icon={Briefcase} label={DETAIL_META.field}>
            {listing.field}
          </DetailMetaRow>
          <DetailMetaRow icon={GraduationCap} label={DETAIL_META.format}>
            {FORMAT_LABELS[listing.format]}
          </DetailMetaRow>
          <DetailMetaRow icon={CalendarDays} label={DETAIL_META.posted}>
            Posted {listing.createdAt}
          </DetailMetaRow>
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
