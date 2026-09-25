"use client";

import type { ReactNode } from "react";
import type { LucideIcon } from "lucide-react";

import Motion from "@/shared/components/animation/Motion";
import { fadeInUp } from "@/shared/lib/animations";
import { cn } from "@/shared/lib/utils";

interface ProfileSectionProps {
  icon: LucideIcon;
  title: string;
  description?: string;
  className?: string;
  children: ReactNode;
}

// ProfileSection: the card shell every profile section sits in — the same
// rounded panel and the same icon + uppercase-label heading the "How
// certificates work" panel uses, so the six sections read as one system
// instead of six one-offs. Presentational: a section composes its own body as
// children. Client-side because the entrance animation is a Motion wrapper and
// because every section that uses it keeps local state.
export function ProfileSection({
  icon: Icon,
  title,
  description,
  className,
  children,
}: ProfileSectionProps) {
  return (
    <Motion
      variants={fadeInUp}
      initial="hidden"
      whileInView="visible"
      viewport={{ once: true, margin: "-40px" }}
      transition={{ duration: 0.24, ease: "easeOut" }}
      className={cn(
        "rounded-2xl border border-border bg-card p-5 shadow-card sm:p-6",
        className,
      )}
    >
      <div className="space-y-1.5">
        <h2 className="flex items-center gap-2 text-sm font-semibold uppercase tracking-wide text-secondary-text">
          <Icon className="size-4" />
          {title}
        </h2>

        {description ? (
          <p className="text-sm leading-relaxed text-muted-foreground">
            {description}
          </p>
        ) : null}
      </div>

      <div className="mt-5">{children}</div>
    </Motion>
  );
}
