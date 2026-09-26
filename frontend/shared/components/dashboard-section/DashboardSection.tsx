import type { ReactNode } from "react";

import Motion from "@/shared/components/animation/Motion";
import { fadeInUp } from "@/shared/lib/animations";
import { cn } from "@/shared/lib/utils";

import {
  ELEVATION_CLASSES,
  SECTION_ENTRANCE,
  SECTION_VIEWPORT,
  type DashboardElevation,
} from "./constants";

interface DashboardSectionProps {
  children: ReactNode;
  elevation?: DashboardElevation;
  // Interactive cards lift on hover; purely informational ones must not
  // promise a click target that isn't there.
  interactive?: boolean;
  className?: string;
}

// DashboardSection: the one card shell every dashboard section sits in —
// surface, radius, padding, elevation tier and entrance animation. Sections
// differ by what they contain, not by re-stating the same box classes.
export function DashboardSection({
  children,
  elevation = "base",
  interactive = false,
  className,
}: DashboardSectionProps) {
  return (
    <Motion
      as="div"
      variants={fadeInUp}
      initial="hidden"
      whileInView="visible"
      viewport={SECTION_VIEWPORT}
      transition={SECTION_ENTRANCE}
      className={cn(
        "flex h-full flex-col rounded-2xl border border-border bg-card p-6",
        ELEVATION_CLASSES[elevation],
        interactive &&
          "transition-shadow duration-200 hover:shadow-lift motion-safe:hover:-translate-y-0.5",
        className,
      )}
    >
      {children}
    </Motion>
  );
}
