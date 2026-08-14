"use client";

import { useState } from "react";
import type { FormEvent } from "react";
import Link from "next/link";
import { useRouter } from "next/navigation";

import { Button } from "@/shared/components/ui/button";

import { TRIAL_MIN_DAYS } from "../../../shared/lib/constants";
import type { ListingMode } from "../../../shared/types";
import { getSessionListings, upsertSessionListing } from "../../lib/listing-session";
import type { MyListingRow } from "../../types";

import { MOCK_MY_LISTINGS } from "../my-listings/constants";
import { BasicInfoFields } from "./BasicInfoFields";
import { EditWarningBanner } from "./EditWarningBanner";
import { HireIntentToggle } from "./HireIntentToggle";
import { ModeAndFormatFields } from "./ModeAndFormatFields";
import { PricingFields } from "./PricingFields";
import {
  CURRENT_COMPANY,
  LISTING_NOT_FOUND,
  NEW_LISTING_STATUS,
  SUBMIT_LABEL,
  VALIDATION_MESSAGES,
} from "./constants";

interface ListingFormProps {
  mode?: "create" | "edit";
  listingId?: string;
}

// Create/edit orchestrator for a company listing (FR-001/004). Owns the
// single per-field controlled exception (`isPaid`, structure rules §10 /
// R-7); everything else is uncontrolled named inputs read off FormData on
// submit. Edit mode prefills from the session store, falling back to the
// mock list, by listingId (R-5). Client-side validation enforces the paid
// price + trial minimum (FR-002/003), then the listing is upserted into the
// UI-only session store (FR-006) and the user returns to My Listings — no
// backend is involved.

export function ListingForm({ mode = "create", listingId }: ListingFormProps) {
  const router = useRouter();

  const existing =
    mode === "edit" && listingId
      ? getSessionListings().find((row) => row.id === listingId) ??
        MOCK_MY_LISTINGS.find((row) => row.id === listingId)
      : undefined;

  const [isPaid, setIsPaid] = useState(existing?.isPaid ?? false);
  const [error, setError] = useState<string | null>(null);

  function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();

    const formData = new FormData(event.currentTarget);

    const field = String(formData.get("field") ?? "").trim();
    const specialization = String(formData.get("specialization") ?? "").trim();
    const description = String(formData.get("description") ?? "").trim();
    const listingMode = String(formData.get("mode") ?? "") as ListingMode;
    const listingFormat = String(formData.get("format") ?? "") as MyListingRow["format"];
    const hireIntent = formData.get("hireIntent") === "true";

    if (!field || !specialization || !listingMode || !listingFormat) {
      setError(VALIDATION_MESSAGES.required);
      return;
    }

    if (isPaid) {
      const price = Number(formData.get("price"));
      const trialDays = Number(formData.get("trialDays"));

      if (!price || price <= 0) {
        setError(VALIDATION_MESSAGES.price);
        return;
      }
      if (!trialDays || trialDays < TRIAL_MIN_DAYS) {
        setError(VALIDATION_MESSAGES.trialDays);
        return;
      }
    }

    setError(null);

    const now = new Date().toISOString();
    const row: MyListingRow = {
      id: existing?.id ?? `new-${Date.now()}`,
      companyId: existing?.companyId ?? CURRENT_COMPANY.id,
      companyName: existing?.companyName ?? CURRENT_COMPANY.name,
      field,
      specialization,
      description: description || undefined,
      mode: listingMode,
      format: listingFormat,
      hireIntent,
      isPaid,
      price: isPaid ? Number(formData.get("price")) : undefined,
      trialDays: isPaid ? Number(formData.get("trialDays")) : undefined,
      status: existing?.status ?? NEW_LISTING_STATUS,
      createdAt: existing?.createdAt ?? now,
      updatedAt: now,
      applicantCount: existing?.applicantCount ?? 0,
      postedDate: existing?.postedDate ?? now.slice(0, 10),
    };

    upsertSessionListing(row);
    router.push("/company/listings");
  }

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

      {error ? (
        <p role="alert" className="text-sm text-error-fg">
          {error}
        </p>
      ) : null}

      <Button type="submit" className="w-full sm:w-auto">
        {mode === "edit" ? SUBMIT_LABEL.edit : SUBMIT_LABEL.create}
      </Button>
    </form>
  );
}
