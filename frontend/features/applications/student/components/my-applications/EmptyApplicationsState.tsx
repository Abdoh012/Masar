import Link from "next/link";

import { Button } from "@/shared/components/ui/button";

interface EmptyApplicationsStateProps {
  title: string;
  message: string;
  ctaHref?: string;
  ctaLabel?: string;
}

// EmptyApplicationsState (FR-026/027, Clarification Q2): the per-tab "nothing
// here" panel shown when the active tab has no applications. Copy comes from
// EMPTY_STATES in constants.ts; the Browse Listings CTA renders only when
// ctaHref is provided (All and Applied tabs only) — Accepted, Rejected, and
// Withdrawn show their message with no CTA. Dashed-panel treatment follows the
// feature's established EmptyState convention. Leaf: receives only the copy it
// renders.
export function EmptyApplicationsState({
  title,
  message,
  ctaHref,
  ctaLabel,
}: EmptyApplicationsStateProps) {
  return (
    <div className="rounded-xl border border-dashed border-border p-10 text-center">
      <p className="font-sans text-base font-semibold text-foreground">{title}</p>
      <p className="mt-1 text-sm text-muted-foreground">{message}</p>
      {ctaHref && ctaLabel ? (
        <div className="mt-4">
          <Button asChild size="sm" variant="outline">
            <Link href={ctaHref}>{ctaLabel}</Link>
          </Button>
        </div>
      ) : null}
    </div>
  );
}
