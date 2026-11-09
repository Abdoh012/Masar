"use client";

import type { ReactNode } from "react";

import Motion from "@/shared/components/animation/Motion";
import { fadeInUp } from "@/shared/lib/animations";
import { cn } from "@/shared/lib/utils";

interface ProfileSectionProps {
  icon: ReactNode;
  title: string;
  description?: string;
  className?: string;
  children: ReactNode;
}

export function ProfileSection({
  icon,
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
          <span className="size-4 shrink-0">{icon}</span>
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
