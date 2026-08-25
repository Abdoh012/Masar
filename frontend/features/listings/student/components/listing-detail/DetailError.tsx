import Link from "next/link";

import { CircleAlert } from "lucide-react";

import { Button } from "@/shared/components/ui/button";

import { DETAIL_COPY } from "./constants";

// DetailError: inline error state for the listing detail page when the API
// fetch fails. Shows an error message with a back-to-browse link.
export function DetailError() {
  return (
    <div className="flex flex-col items-center gap-4 rounded-2xl border border-border bg-card p-10 text-center">
      <span className="grid size-12 place-items-center rounded-full bg-error-bg text-error-fg">
        <CircleAlert className="size-6" strokeWidth={2} />
      </span>
      <p className="text-sm font-medium text-primary-text">
        Failed to load training
      </p>
      <p className="text-sm text-muted-foreground">
        Something went wrong while fetching the listing. Please try again.
      </p>
      <Button asChild variant="outline" className="mt-2">
        <Link href="/listings">{DETAIL_COPY.backToBrowse}</Link>
      </Button>
    </div>
  );
}
