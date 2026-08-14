"use client";

import Link from "next/link";

import { Button } from "@/shared/components/ui/button";

import type { MyApplication } from "../../types";
import { CARD_ACTION_LABELS } from "./constants";

interface ApplicationCardActionsProps {
  application: MyApplication;
  onWithdraw: (application: MyApplication) => void;
}

// Card actions (FR-015-018/025). View Listing is a shared Button-as-Link to the
// listing, rendered for every status; Withdraw appears only on pending
// ("Applied") applications and merely surfaces the UI entry point — the confirm
// dialog and the status transition are wired in US4 (T013-T014). No Open Chat
// control exists anywhere on the page (FR-020).
export function ApplicationCardActions({
  application,
  onWithdraw,
}: ApplicationCardActionsProps) {
  return (
    <div className="flex flex-wrap items-center gap-2">
      <Button asChild size="sm" variant="outline">
        <Link href={`/listings/${application.listingId}`}>
          {CARD_ACTION_LABELS.viewListing}
        </Link>
      </Button>

      {application.status === "Applied" ? (
        <Button size="sm" variant="destructive" onClick={() => onWithdraw(application)}>
          {CARD_ACTION_LABELS.withdraw}
        </Button>
      ) : null}
    </div>
  );
}