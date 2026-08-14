"use client";

import { useState } from "react";
import type { FormEvent } from "react";

import { Button } from "@/shared/components/ui/button";
import { Input } from "@/shared/components/ui/input";
import { Label } from "@/shared/components/ui/label";

import { APPLY_COPY } from "./constants";

interface ApplyCtaProps {
  listingId: string;
  appliedByDefault?: boolean;
}

// Student apply CTA (FR-016). "use client": produces a local success/applied
// state only — no real submission, no network call (R-6/R-8). If the mock
// student already applied, the CTA renders the already-applied status
// instead (FR-017).

export function ApplyCta({ listingId, appliedByDefault = false }: ApplyCtaProps) {
  const [submitted, setSubmitted] = useState(false);
  const alreadyApplied = appliedByDefault || submitted;

  function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setSubmitted(true);
  }

  if (alreadyApplied) {
    return (
      <div role="status" className="rounded-xl border border-primary-tint bg-primary-tint/50 p-5">
        <p className="flex items-center gap-2 text-sm font-semibold text-primary-text">
          <span aria-hidden="true" className="text-base leading-none">✓</span>
          {APPLY_COPY.applied}
        </p>
        <p className="mt-1 text-sm text-muted-foreground">{APPLY_COPY.appliedMessage}</p>
      </div>
    );
  }

  return (
    <form onSubmit={handleSubmit} className="space-y-4">
      <h2 className="font-sans text-base font-semibold text-foreground">{APPLY_COPY.title}</h2>
      <div className="space-y-1.5">
        <Label htmlFor={`apply-note-${listingId}`}>{APPLY_COPY.noteLabel}</Label>
        <Input
          id={`apply-note-${listingId}`}
          name="note"
          placeholder={APPLY_COPY.notePlaceholder}
        />
      </div>
      <Button type="submit">{APPLY_COPY.button}</Button>
    </form>
  );
}
