"use client";

import type { ReactNode } from "react";
import type { LucideIcon } from "lucide-react";

import { cn } from "@/shared/lib/utils";

interface ProfileSubPanelProps {
  icon: LucideIcon;
  title: string;
  description?: string;
  className?: string;
  children: ReactNode;
}

// ProfileSubPanel: the inset panel used for the grouped blocks inside a section
// (username vs. password, skills vs. education). One step down from
// ProfileSection: a tinted icon tile with a sentence-case title instead of the
// uppercase eyebrow, and a darker surface so the nesting reads.
export function ProfileSubPanel({
  icon: Icon,
  title,
  description,
  className,
  children,
}: ProfileSubPanelProps) {
  return (
    <section
      className={cn(
        "flex flex-col rounded-xl border border-border bg-background p-4 sm:p-5",
        className,
      )}
    >
      <div className="flex items-start gap-3">
        <span className="grid size-9 shrink-0 place-items-center rounded-lg bg-primary-tint text-primary-text">
          <Icon className="size-4" />
        </span>

        <div className="min-w-0 flex-1">
          <h3 className="text-sm font-semibold text-primary-text">{title}</h3>
          {description ? (
            <p className="mt-1 text-xs leading-relaxed text-muted-foreground">
              {description}
            </p>
          ) : null}
        </div>
      </div>

      <div className="mt-4 flex-1">{children}</div>
    </section>
  );
}
