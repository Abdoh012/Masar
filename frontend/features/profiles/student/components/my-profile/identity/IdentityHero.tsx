import { CalendarDays, GraduationCap, Sparkles } from "lucide-react";

import Motion from "@/shared/components/animation/Motion";
import { fadeInUp } from "@/shared/lib/animations";

import type { ProfileIdentity } from "../../../types";
import { formatMonthYear } from "../../../lib/format";
import { AvatarEditor } from "./AvatarEditor";

interface IdentityHeroProps {
  profile: ProfileIdentity;
}

export function IdentityHero({ profile }: IdentityHeroProps) {
  const {
    name,
    initials,
    specialization,
    field,
    institution,
    memberSince,
    avatarUrl,
  } = profile;

  const hasStudies = Boolean(field || institution);

  return (
    <Motion
      variants={fadeInUp}
      initial="hidden"
      whileInView="visible"
      viewport={{ once: true, margin: "-40px" }}
      transition={{ duration: 0.24, ease: "easeOut" }}
      className="relative overflow-hidden rounded-2xl border border-border bg-card p-5 shadow-card sm:p-6"
    >
      {/* Same decorative wash as the shared PageHeader, to lift the hero off the page */}
      <div className="pointer-events-none absolute inset-0 bg-linear-to-br from-secondary/10 via-transparent to-primary/10" />
      <div className="pointer-events-none absolute -right-24 -top-28 size-56 rounded-full bg-secondary/15 blur-3xl" />

      <div className="relative flex flex-col items-center gap-5 text-center sm:flex-row sm:gap-6 sm:text-left">
        <AvatarEditor name={name} initials={initials} src={avatarUrl} />

        <div className="min-w-0 space-y-3">
          {/* The page's <h1> — /profile has no PageHeader band above this hero. */}
          <h1 className="font-sans text-2xl font-semibold leading-tight text-primary-text sm:text-3xl">
            {name}
          </h1>

          {specialization ? (
            <span className="inline-flex items-center gap-1.5 rounded-full bg-secondary-tint px-2.5 py-1 text-xs font-semibold text-secondary-text">
              <Sparkles className="size-3.5" />
              {specialization}
            </span>
          ) : null}

          {hasStudies ? (
            <p className="flex flex-wrap items-center justify-center gap-x-1.5 gap-y-1 text-sm text-muted-foreground sm:justify-start">
              <GraduationCap className="size-4 shrink-0" />
              {field ? <span>{field}</span> : null}
              {field && institution ? (
                <span aria-hidden className="text-disabled">
                  ·
                </span>
              ) : null}
              {institution ? <span>{institution}</span> : null}
            </p>
          ) : null}

          {memberSince ? (
            <p className="flex items-center justify-center gap-1.5 text-xs text-muted-foreground sm:justify-start">
              <CalendarDays className="size-3.5 shrink-0" />
              Member Since
              <span className="font-mono text-foreground">
                {formatMonthYear(memberSince)}
              </span>
            </p>
          ) : null}
        </div>
      </div>
    </Motion>
  );
}
