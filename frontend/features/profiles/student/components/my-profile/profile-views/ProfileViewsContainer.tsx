"use client";

import { useState } from "react";
import { ChevronDown, Eye } from "lucide-react";

import Motion from "@/shared/components/animation/Motion";
import { fadeInUp } from "@/shared/lib/animations";
import { Button } from "@/shared/components/ui/button";
import { cn } from "@/shared/lib/utils";

import { PROFILE_VIEWERS, PROFILE_VIEW_COUNT } from "../constants";
import { PROFILE_VIEWS_LABELS } from "./constants";
import { ProfileSection } from "../ProfileSection";
import { ViewerRow } from "./ViewerRow";

// ProfileViewsContainer: the headline view count plus a collapsible list of the
// most recent viewers. The count band is always visible; the list mounts only
// while expanded, so collapsed rows are never in the tab order or read by a
// screen reader. Placeholder data for now — the real count and viewer list come
// from the profile API (see student/api.ts once it exists).
export default function ProfileViewsContainer() {
  const [isExpanded, setIsExpanded] = useState(false);

  return (
    <ProfileSection
      icon={Eye}
      title={PROFILE_VIEWS_LABELS.title}
      description={PROFILE_VIEWS_LABELS.description}
    >
      <div className="flex flex-col gap-4 rounded-xl border border-border bg-background p-4 sm:flex-row sm:items-center sm:justify-between sm:p-5">
        <div className="flex items-center gap-4">
          <span className="grid size-11 shrink-0 place-items-center rounded-xl bg-primary-tint text-primary-text">
            <Eye className="size-5" />
          </span>

          <div className="min-w-0">
            <p className="text-2xl font-bold leading-none text-primary-text sm:text-3xl">
              {PROFILE_VIEW_COUNT}
            </p>
            <p className="mt-1.5 text-sm text-muted-foreground">
              {PROFILE_VIEWS_LABELS.count}
            </p>
          </div>
        </div>

        <Button
          type="button"
          variant="outline"
          size="sm"
          className="w-full cursor-pointer sm:w-auto"
          onClick={() => setIsExpanded((expanded) => !expanded)}
          aria-expanded={isExpanded}
        >
          {isExpanded
            ? PROFILE_VIEWS_LABELS.hide
            : PROFILE_VIEWS_LABELS.show}
          <ChevronDown
            className={cn(
              "size-4 transition-transform duration-200",
              isExpanded && "rotate-180",
            )}
          />
        </Button>
      </div>

      {isExpanded ? (
        <Motion
          variants={fadeInUp}
          initial="hidden"
          animate="visible"
          transition={{ duration: 0.24, ease: "easeOut" }}
          className="mt-4"
        >
          <ul className="grid gap-2 sm:grid-cols-2">
            {PROFILE_VIEWERS.map((viewer) => (
              <ViewerRow key={viewer.id} viewer={viewer} />
            ))}
          </ul>

          <p className="mt-3 text-xs text-muted-foreground">
            {PROFILE_VIEWS_LABELS.listNote}
          </p>
        </Motion>
      ) : null}
    </ProfileSection>
  );
}
