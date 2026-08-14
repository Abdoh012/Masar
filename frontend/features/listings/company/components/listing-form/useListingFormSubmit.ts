"use client";

import { useState } from "react";
import type { FormEvent } from "react";
import { useRouter } from "next/navigation";

import { TRIAL_MIN_DAYS } from "../../../shared/lib/constants";
import type { ListingMode } from "../../../shared/types";
import { upsertSessionListing } from "../../lib/listing-session";
import type { MyListingRow } from "../../types";

import {
  CURRENT_COMPANY,
  NEW_LISTING_STATUS,
  VALIDATION_MESSAGES,
} from "./constants";

interface UseListingFormSubmitOptions {
  existing?: MyListingRow;
  isPaid: boolean;
}

// useListingFormSubmit: owns the create/edit listing submission mechanics —
// client-side validation (FR-002/003), the error feedback state, the session
// upsert (FR-006), and the redirect back to My Listings (structure rules §17).
// The form's uncontrolled named inputs are read off FormData here; the
// orchestrator keeps only the single controlled `isPaid` exception and passes
// it in.
export function useListingFormSubmit({ existing, isPaid }: UseListingFormSubmitOptions) {
  const router = useRouter();
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

  return { error, handleSubmit };
}
