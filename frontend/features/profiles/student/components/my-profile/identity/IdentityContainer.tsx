"use client";

import { CalendarDays, GraduationCap, Sparkles } from "lucide-react";

import Motion from "@/shared/components/animation/Motion";
import { fadeInUp } from "@/shared/lib/animations";

import { formatMonthYear } from "../../../lib/format";
import { AvatarEditor } from "./AvatarEditor";
import { IDENTITY_LABELS } from "./constants";
import type { ProfileIdentity } from "../../../types";

interface IdentityContainerProps {
  profile: ProfileIdentity;
}

// IdentityContainer: the page's hero card — avatar, name, specialization and
// field, plus the muted "Member since" line. It is deliberately not a
// ProfileSection: instead of the section eyebrow it carries the same
// token-only wash and gold corner glow the shared PageHeader uses, which is what
// makes it the most prominent panel on the page. Stacks centered on mobile and
// goes side-by-side from sm up. Owns composition only — the photo is the
// AvatarEditor leaf.
export default function IdentityContainer({ profile }: IdentityContainerProps) {
  const { name, initials, specialization, field, institution, memberSince } =
    profile;

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
        <AvatarEditor name={name} initials={initials} src={profile.avatarUrl} />

        <div className="min-w-0 space-y-3">
          {/* Not an <h1> — the shared PageHeader already owns the page title. */}
          <p className="font-sans text-2xl font-semibold leading-tight text-primary-text sm:text-3xl">
            {name}
          </p>

          <span className="inline-flex items-center gap-1.5 rounded-full bg-secondary-tint px-2.5 py-1 text-xs font-semibold text-secondary-text">
            <Sparkles className="size-3.5" />
            {specialization}
          </span>

          <p className="flex flex-wrap items-center justify-center gap-x-1.5 gap-y-1 text-sm text-muted-foreground sm:justify-start">
            <GraduationCap className="size-4 shrink-0" />
            <span>{field}</span>
            <span aria-hidden className="text-disabled">
              ·
            </span>
            <span>{institution}</span>
          </p>

          <p className="flex items-center justify-center gap-1.5 text-xs text-muted-foreground sm:justify-start">
            <CalendarDays className="size-3.5 shrink-0" />
            {IDENTITY_LABELS.memberSince}
            <span className="font-mono text-foreground">
              {formatMonthYear(memberSince)}
            </span>
          </p>
        </div>
      </div>
    </Motion>
  );
}
