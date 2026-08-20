import Link from "next/link";
import Image from "next/image";
import { ArrowRight, Briefcase } from "lucide-react";

import { cn } from "@/shared/lib/utils";
import type { ListingCardData } from "@/features/listings/shared/types";
import { ModeBadge } from "@/features/listings/shared/components/mode-badge/ModeBadge";
import { PaidBadge } from "@/features/listings/shared/components/paid-badge/PaidBadge";

import { CARD_ACTION_LABEL } from "./constants";
import { CardMeta } from "./CardMeta";
import { CategoryPill } from "./CategoryPill";
import { SkillTags } from "./SkillTags";

// ListingCard: the one shared listing card, reused by student browse and the
// dashboard's Recommended Listings (FR-022 — no second card definition).
// Consumes ListingCardData (UI-only display fields). Pure leaf: narrow props,
// no fetching, no state.
export function ListingCard({ ...listing }: ListingCardData & { className?: string }) {
  const {
    id,
    field,
    specialization,
    companyName,
    description,
    mode,
    isPaid,
    trialDays,
    skills,
    duration,
    format,
    createdAt,
    className,
  } = listing;

  return (
    <article
      className={cn(
        "flex h-full flex-col gap-3 rounded-2xl border border-border bg-card p-5 shadow-card",
        className,
      )}
    >
      <div className="flex items-start justify-between gap-3">
        <CategoryPill field={field} />
        <Image
          src="/logo.png"
          alt=""
          width={48}
          height={48}
          className="size-12 shrink-0 rounded-xl bg-neutral-50 object-contain p-1"
        />
      </div>

      <h3 className="line-clamp-2 text-base font-semibold text-primary-text">{specialization}</h3>

      <div className="flex flex-wrap items-center gap-2">
        <ModeBadge mode={mode} />
        <PaidBadge isPaid={isPaid} trialDays={trialDays} />
      </div>

      <p className="flex items-center gap-1.5 text-sm text-muted-foreground">
        <Briefcase aria-hidden="true" className="size-4 shrink-0" />
        <span className="truncate">{companyName}</span>
      </p>

      {description ? (
        <p className="line-clamp-3 text-sm text-muted-foreground">{description}</p>
      ) : null}

      {skills && skills.length > 0 ? <SkillTags skills={skills} /> : null}

      <div className="mt-auto flex flex-col gap-3 pt-1">
        <CardMeta duration={duration} format={format} createdAt={createdAt} />

        <Link
          href={`/listings/${id}`}
          className="group inline-flex items-center gap-1.5 text-sm font-medium text-primary transition-colors hover:text-primary/80 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
        >
          {CARD_ACTION_LABEL}
          <ArrowRight
            aria-hidden="true"
            className="size-4 transition-transform group-hover:translate-x-0.5"
          />
        </Link>
      </div>
    </article>
  );
}