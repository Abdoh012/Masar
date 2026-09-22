import Link from "next/link";

import { Button } from "@/shared/components/ui/button";

import type { ApplicationStatus } from "../../types";

import { AppliedPanel } from "./AppliedPanel";
import { APPLY_COPY } from "./constants";

interface ApplyCtaProps {
  listingId: string;
  applicationStatus?: ApplicationStatus;
}

// Student apply CTA (FR-016). Server leaf: any existing application renders
// the status panel (FR-017) with a status-specific message; otherwise it's a
// link into the application wizard route (features/applications).
export function ApplyCta({ listingId, applicationStatus }: ApplyCtaProps) {
  if (applicationStatus) {
    return <AppliedPanel status={applicationStatus} />;
  }

  return (
    <Button asChild size="lg" className="w-full">
      <Link href={`/listings/${listingId}/apply`}>{APPLY_COPY.button}</Link>
    </Button>
  );
}