import Link from "next/link";

import { CheckCircle2 } from "lucide-react";

import { Button } from "@/shared/components/ui/button";

import { APPLY_COPY } from "./constants";

interface ApplyCtaProps {
  listingId: string;
  appliedByDefault?: boolean;
}

// Student apply CTA (FR-016). Server leaf: the already-applied marker renders
// the status panel (FR-017); otherwise it's a link into the application wizard
// route (features/applications), which owns the real apply flow. The wizard is
// UI-only too, so the link is a plain client navigation — no form, no state
// here (structure rules §4/§8).
export function ApplyCta({ listingId, appliedByDefault = false }: ApplyCtaProps) {
  if (appliedByDefault) {
    return (
      <div role="status" className="rounded-xl border border-primary-tint bg-primary-tint/50 p-5">
        <p className="flex items-center gap-2 text-sm font-semibold text-primary-text">
          <CheckCircle2 aria-hidden="true" className="size-5 text-primary" />
          {APPLY_COPY.applied}
        </p>
        <p className="mt-1 text-sm text-muted-foreground">{APPLY_COPY.appliedMessage}</p>
      </div>
    );
  }

  return (
    <Button asChild size="lg" className="w-full">
      <Link href={`/listings/${listingId}/apply`}>{APPLY_COPY.button}</Link>
    </Button>
  );
}