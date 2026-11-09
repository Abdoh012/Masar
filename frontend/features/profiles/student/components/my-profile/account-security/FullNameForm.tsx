"use client";

import { Info, UserRound } from "lucide-react";

import { Input } from "@/shared/components/ui/input";
import { Label } from "@/shared/components/ui/label";
import { useFormFeedBack } from "@/shared/hooks/useFormFeedback";

import { updateFullName } from "../../../actions";
import type { FullNameFormValues } from "../../../types";
import { AccountSubmitButton } from "./AccountSubmitButton";
import { ACCOUNT_SECURITY_LABELS } from "./constants";

interface FullNameFormProps {
  fullName: string | null;
}

export function FullNameForm({ fullName }: FullNameFormProps) {
  const { formAction, state } = useFormFeedBack(updateFullName, null);
  const hasNameError = Boolean(state?.fieldErrors?.full_name?.length);

  const restored = state?.data as FullNameFormValues | undefined;
  const defaultValue = restored?.fullName ?? fullName ?? "";

  return (
    <form action={formAction} className="space-y-4">
      <div className="space-y-1.5">
        <Label htmlFor="profile-full-name">
          {ACCOUNT_SECURITY_LABELS.fullName.label}
        </Label>

        <div className="relative">
          <UserRound className="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />

          <Input
            id="profile-full-name"
            name="full_name"
            type="text"
            required
            maxLength={255}
            autoComplete="name"
            defaultValue={defaultValue}
            placeholder={ACCOUNT_SECURITY_LABELS.fullName.placeholder}
            aria-invalid={hasNameError}
            className="pl-10"
          />
        </div>
      </div>

      <p className="flex items-start gap-1.5 text-xs leading-relaxed text-muted-foreground">
        <Info className="mt-0.5 size-3.5 shrink-0" />
        {ACCOUNT_SECURITY_LABELS.fullName.hint}
      </p>

      <div className="flex justify-end">
        <AccountSubmitButton
          label={ACCOUNT_SECURITY_LABELS.fullName.save}
          pendingLabel={ACCOUNT_SECURITY_LABELS.fullName.saving}
        />
      </div>
    </form>
  );
}
