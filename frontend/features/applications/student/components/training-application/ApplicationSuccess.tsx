import Link from "next/link";

import { Check } from "lucide-react";

import { Button } from "@/shared/components/ui/button";

import { SUCCESS_COPY } from "./constants";

interface ApplicationSuccessProps {
  listingTitle: string;
  companyName: string;
}

// ApplicationSuccess: the post-submit confirmation panel. Uses the shared
// success visual language (success-bg/success-fg + Check icon) — sage stays
// reserved for hire signals only. Leaf: renders copy + CTAs, no state.
export function ApplicationSuccess({ listingTitle, companyName }: ApplicationSuccessProps) {
  return (
    <div className="flex flex-col items-center gap-5 py-10 text-center">
      <span className="grid size-16 place-items-center rounded-full bg-success-bg text-success-fg">
        <Check className="size-8" />
      </span>

      <div className="space-y-2">
        <h2 className="font-sans text-xl font-semibold text-primary-text">
          {SUCCESS_COPY.title}
        </h2>
        <p className="mx-auto max-w-md text-sm leading-relaxed text-muted-foreground">
          {SUCCESS_COPY.message(listingTitle, companyName)}
        </p>
      </div>

      <div className="flex flex-col gap-3 sm:flex-row">
        <Button asChild>
          <Link href="/listings">{SUCCESS_COPY.backToBrowse}</Link>
        </Button>
        <Button asChild variant="outline">
          <Link href="/applications">{SUCCESS_COPY.viewApplications}</Link>
        </Button>
      </div>
    </div>
  );
}