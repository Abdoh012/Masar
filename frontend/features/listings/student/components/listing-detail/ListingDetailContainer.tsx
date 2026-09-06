import Link from "next/link";
import { notFound } from "next/navigation";

import { Briefcase, Building2, CalendarDays, GraduationCap } from "lucide-react";

import { ModeBadge } from "@/features/listings/shared/components/mode-badge/ModeBadge";
import { PaidBadge } from "@/features/listings/shared/components/paid-badge/PaidBadge";
import { SaveButton } from "@/features/listings/shared/components/listing-card/SaveButton";
import { FORMAT_LABELS } from "@/features/listings/shared/lib/constants";
import { Button } from "@/shared/components/ui/button";

import { fetchTrainingDetails } from "../../api";
import { normalizeApiItem } from "../../lib/normalize";

import { ApplyCta } from "./ApplyCta";
import { DetailMetaRow } from "./DetailMetaRow";
import { DETAIL_COPY, DETAIL_META } from "./constants";

interface ListingDetailContainerProps {
  id: string;
}

// ListingDetailContainer: server orchestrator for the listing detail page.
// Resolves the training from the URL id (404 → notFound) and composes the
// detail card, apply CTA and save toggle. No client state — save/unsave
// revalidates the route so this component re-renders with the fresh is_saved.
export async function ListingDetailContainer({ id }: ListingDetailContainerProps) {
  const raw = await fetchTrainingDetails(id);
  if (raw.success === false) {
    if (raw.status === 404) notFound();
    throw new Error(raw.error ?? "Failed to load training");
  }

  const listing = normalizeApiItem(raw.data);
  const listingId = listing.id;
  const alreadyApplied = listing.hasApplied ?? false;

  return (
    <article className="space-y-8">
      <div className="space-y-4 rounded-2xl border border-border bg-card p-6">
        <div className="flex flex-wrap items-center gap-2">
          <ModeBadge mode={listing.mode} />
          <PaidBadge isPaid={listing.isPaid} trialDays={listing.trialDays} price={listing.price} currency={listing.currency} />

          <div className="ml-auto">
            <SaveButton saved={listing.saved} id={listingId} />
          </div>
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