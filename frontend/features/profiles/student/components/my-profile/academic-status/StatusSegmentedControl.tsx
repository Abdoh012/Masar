"use client";

import type { LucideIcon } from "lucide-react";
import { BookOpen, GraduationCap } from "lucide-react";

import Motion from "@/shared/components/animation/Motion";
import { cn } from "@/shared/lib/utils";

import { ACADEMIC_LEVELS, ACADEMIC_STATUS_LABELS } from "./constants";
import type { AcademicLevel } from "../../../types";

const LEVEL_ICONS: Record<AcademicLevel, LucideIcon> = {
  student: BookOpen,
  graduate: GraduationCap,
};

interface StatusSegmentedControlProps {
  value: AcademicLevel;
  onChange: (value: AcademicLevel) => void;
}

// StatusSegmentedControl: the Student / Graduate switch. A radio group over a
// pill track, with the active segment's navy background on a shared-Motion
// layoutId so it slides between options instead of snapping — the same trick the
// applications status tabs use. max-w-sm keeps it from stretching across a
// desktop panel where a two-option switch has no business being that wide.
export function StatusSegmentedControl({
  value,
  onChange,
}: StatusSegmentedControlProps) {
  return (
    <div
      role="radiogroup"
      aria-label={ACADEMIC_STATUS_LABELS.groupLabel}
      className="inline-flex w-full max-w-sm rounded-full border border-border bg-background p-1"
    >
      {ACADEMIC_LEVELS.map((option) => {
        const Icon = LEVEL_ICONS[option.value];
        const isActive = option.value === value;

        return (
          <button
            key={option.value}
            type="button"
            role="radio"
            aria-checked={isActive}
            title={option.description}
            onClick={() => onChange(option.value)}
            className={cn(
              "relative flex flex-1 cursor-pointer items-center justify-center gap-2 rounded-full px-4 py-2 text-sm font-medium transition-colors focus-visible:outline-none",
              isActive
                ? "text-primary-foreground"
                : "text-muted-foreground hover:text-primary-text",
            )}
          >
            {isActive ? (
              <Motion
                layoutId="academic-status-segment"
                className="absolute inset-0 rounded-full bg-primary"
                transition={{ duration: 0.25, ease: "easeOut" }}
              />
            ) : null}

            <span className="relative z-10 flex items-center gap-2">
              <Icon className="size-4" />
              {option.label}
            </span>
          </button>
        );
      })}
    </div>
  );
}
