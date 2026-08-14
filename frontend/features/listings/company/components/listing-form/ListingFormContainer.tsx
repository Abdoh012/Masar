"use client";

import { useState } from "react";
import Link from "next/link";

import { Button } from "@/shared/components/ui/button";

import { getSessionListings } from "../../lib/listing-session";

import { MOCK_MY_LISTINGS } from "../my-listings/constants";
import { BasicInfoFields } from "./BasicInfoFields";
import { EditWarningBanner } from "./EditWarningBanner";
import { FormSubmitButton } from "./FormSubmitButton";
import { HireIntentToggle } from "./HireIntentToggle";
import { ModeAndFormatFields } from "./ModeAndFormatFields";
import { PricingFields } from "./PricingFields";
import { useListingFormSubmit } from "./useListingFormSubmit";
import { LISTING_NOT_FOUND } from "./constants";

interface ListingFormContainerProps {
  mode?: "create" | "edit";
  listingId?: string;
}

// Create/edit listing orchestrator (FR-001/004). Composes the presentational
// field sections, owns the single per-field controlled exception (`isPaid`,
// structure rules §10 / R-7), and delegates all submission/feedback mechanics
// to useListingFormSubmit (§17). Edit mode prefills from the session store,
// falling back to the mock list, by listingId (R-5).

export function ListingFormContainer({ mode = "create", listingId }: ListingFormContainerProps) {
  const existing =
    mode === "edit" && listingId
      ? getSessionListings().find((row) => row.id === listingId) ??
        MOCK_MY_LISTINGS.find((row) => row.id === listingId)
      : undefined;

  const [isPaid, setIsPaid] = useState(existing?.isPaid ?? false);
  const { error, handleSubmit } = useListingFormSubmit({ existing, isPaid });

  if (mode === "edit" && !existing) {
    return (
      <div className="space-y-4">
        <h2 className="font-sans text-lg font-semibold text-foreground">
          {LISTING_NOT_FOUND.title}
        </h2>
        <p className="text-sm text-muted-foreground">{LISTING_NOT_FOUND.message}</p>
        <Button asChild variant="outline">
          <Link href="/company/listings">{LISTING_NOT_FOUND.backLabel}</Link>
        </Button>
      </div>
    );
  }

  return (
    <form className="space-y-8" onSubmit={handleSubmit} noValidate>
      {mode === "edit" && existing && existing.applicantCount > 0 ? (
        <EditWarningBanner />
      ) : null}

      <BasicInfoFields
        defaultField={existing?.field}
        defaultSpecialization={existing?.specialization}
        defaultDescription={existing?.description}
      />
      <ModeAndFormatFields defaultMode={existing?.mode} defaultFormat={existing?.format} />
      <PricingFields
        isPaid={isPaid}
        onPaidChange={setIsPaid}
        defaultPrice={existing?.price}
        defaultTrialDays={existing?.trialDays}
      />
      <HireIntentToggle defaultChecked={existing?.hireIntent} />

      <FormSubmitButton mode={mode} error={error} />
    </form>
  );
}
