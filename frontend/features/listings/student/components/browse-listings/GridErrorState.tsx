"use client";

import Link from "next/link";

import { CircleAlert } from "lucide-react";

import Motion from "@/shared/components/animation/Motion";
import { Button } from "@/shared/components/ui/button";
import { fadeInUp } from "@/shared/lib/animations";

import { GRID_ERROR_COPY } from "./constants";

interface GridErrorStateProps {
  onRetry: () => void;
}

// GridErrorState: scoped retry panel for the browse grid, rendered by the
// GridErrorBoundary when the card fetch fails. Only the grid is replaced —
// hero, filters and pagination context stay mounted. Retry re-renders the
// BrowseResults subtree (fresh server render → fresh fetch).
export function GridErrorState({ onRetry }: GridErrorStateProps) {
  return (
    <section
      role="alert"
      className="mt-6 rounded-2xl border border-border bg-card px-6 py-16 text-center"
    >
      <Motion
        variants={fadeInUp}
        initial="hidden"
        animate="visible"
        className="flex flex-col items-center gap-4"
      >
        <span className="grid size-12 place-items-center rounded-full bg-error-bg text-error-fg">
          <CircleAlert className="h-6 w-6" strokeWidth={2} />
        </span>
        <h2 className="text-lg font-semibold text-primary-text">
          {GRID_ERROR_COPY.title}
        </h2>
        <p className="max-w-sm text-sm leading-relaxed text-muted-foreground">
          {GRID_ERROR_COPY.message}
        </p>
        <div className="mt-2 flex flex-col items-stretch gap-2 sm:flex-row">
          <Button onClick={onRetry}>{GRID_ERROR_COPY.retry}</Button>
          <Button asChild variant="outline">
            <Link href="/listings">{GRID_ERROR_COPY.backToBrowse}</Link>
          </Button>
        </div>
      </Motion>
    </section>
  );
}