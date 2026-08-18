import Link from "next/link";
import Image from "next/image";
import { ArrowRight, Briefcase } from "lucide-react";

import type { ListingCardData } from "@/features/listings/shared/types";
import { ModeBadge } from "@/features/listings/shared/components/mode-badge/ModeBadge";
import { PaidBadge } from "@/features/listings/shared/components/paid-badge/PaidBadge";

import { CARD_ACTION_LABEL } from "./constants";
import { CardMeta } from "./CardMeta";
import { CategoryPill } from "./CategoryPill";
import { SkillTags } from "./SkillTags";

export function ListingCard({
  ...listing
}: ListingCardData & { className?: string }) {
  return (
    <article
      className={`flex h-full flex-col gap-3 rounded-2xl border border-border bg-card p-5 shadow-card ${listing.className}`}
    >
      <div className="flex items-center justify-between gap-3">
        <CategoryPill field={listing.field} />

        <Image
          src="/logo.png"
          alt=""
          width={48}
          height={48}
          className="size-12 shrink-0 rounded-xl bg-neutral-50"
        />
      </div>

      <h3 className="text-base font-semibold text-primary-text">
        {listing.specialization}
      </h3>

      <div className="flex flex-wrap items-center gap-2">
        <ModeBadge mode={listing.mode} />
        <PaidBadge isPaid={listing.isPaid} trialDays={listing.trialDays} />
      </div>

      <p className="flex items-center gap-1.5 text-sm text-muted-foreground">
        <Briefcase className="size-4 shrink-0" />
        <span className="truncate">{listing.companyName}</span>
      </p>

      {listing.description ? (
        <p className="line-clamp-3 text-sm text-muted-foreground">
          {listing.description}
        </p>
      ) : null}

      {listing.skills && listing.skills.length > 0 ? (
        <SkillTags skills={listing.skills} />
      ) : null}

      <div className="mt-auto flex flex-col gap-3 pt-1">
        <CardMeta
          duration={listing.duration}
          format={listing.format}
          createdAt={listing.createdAt}
        />

        <Link
          href={`/listings/${listing.id}`}
          className="group inline-flex items-center gap-1.5 text-sm font-medium text-primary transition-colors hover:text-primary/80 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
        >
          {CARD_ACTION_LABEL}
          <ArrowRight className="size-4 transition-transform group-hover:translate-x-0.5" />
        </Link>
      </div>
    </article>
  );
}
