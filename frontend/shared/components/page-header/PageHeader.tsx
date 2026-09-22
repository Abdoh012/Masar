// PageHeader: shared page-level header band used by the Trainings, Applications,
// and Certificates pages (promoted to shared/ once three features consumed it).
// Owns the band styling, entrance animation, and eyebrow/title/description
// hierarchy; each page supplies its own copy plus an optional icon (pre-rendered
// ReactNode, never a component reference) and right-aligned actions (e.g. count
// chips). All colors are semantic tokens so the band flips with dark mode.
import type { ReactNode } from "react";

import Motion from "@/shared/components/animation/Motion";
import { fadeInUp } from "@/shared/lib/animations";

interface PageHeaderProps {
  eyebrow: string;
  title: string;
  description?: string;
  icon?: ReactNode;
  actions?: ReactNode;
}

export function PageHeader({
  eyebrow,
  title,
  description,
  icon,
  actions,
}: PageHeaderProps) {
  return (
    <Motion
      variants={fadeInUp}
      initial="hidden"
      whileInView="visible"
      viewport={{ once: true }}
      className="relative overflow-hidden rounded-2xl border border-border bg-card px-5 py-8 shadow-card sm:px-8 sm:py-10"
    >
      {/* Soft token-only decorative wash + corner glow to lift the band off the flat page background */}
      <div className="pointer-events-none absolute inset-0 bg-linear-to-br from-secondary/10 via-transparent to-primary/10" />
      <div className="pointer-events-none absolute -right-24 -top-28 size-56 rounded-full bg-secondary/15 blur-3xl" />

      <div className="relative flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between sm:gap-8">
        <div className="flex flex-col gap-4 sm:flex-row sm:items-center">
          {icon ? (
            <div className="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-primary-tint text-primary-text sm:size-14">
              {icon}
            </div>
          ) : null}

          <div className="space-y-2.5">
            <p className="flex items-center gap-3 text-xs font-semibold uppercase tracking-[0.22em] text-secondary-text">
              <span className="h-px w-8 bg-secondary" />
              {eyebrow}
            </p>
            <h1 className="font-sans text-3xl font-semibold leading-tight text-primary-text sm:text-4xl">
              {title}
            </h1>
            {description ? (
              <p className="max-w-2xl text-sm leading-relaxed text-muted-foreground sm:text-base">
                {description}
              </p>
            ) : null}
          </div>
        </div>

        {actions ? (
          <div className="flex shrink-0 items-center gap-2">{actions}</div>
        ) : null}
      </div>
    </Motion>
  );
}
