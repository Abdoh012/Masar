"use client";

import { useEffect } from "react";

import { Button } from "@/shared/components/ui/button";
import { useFormFeedBack } from "@/shared/hooks/useFormFeedback";

import { updateContactInfo } from "../../../actions";
import type { ContactInfo } from "../../../types";
import { CONTACT_INFO_LABELS, CONTACT_FIELDS } from "./constants";
import { AccountSubmitButton } from "../account-security/AccountSubmitButton";
import { ProfileFieldInput } from "../ProfileFieldInput";

interface ContactInfoFormProps {
  contact: ContactInfo;
  onSaved: () => void;
}

export function ContactInfoForm({ contact, onSaved }: ContactInfoFormProps) {
  const { formAction, state, isPending } = useFormFeedBack(
    updateContactInfo,
    null,
  );

  useEffect(() => {
    if (state?.success) {
      onSaved();
    }
  }, [state?.success, onSaved]);

  const restored =
    state && !state.success
      ? (state.data as ContactInfo | undefined)
      : undefined;

  return (
    <form action={formAction} className="space-y-4">
      <div className="grid gap-4 sm:grid-cols-2">
        {CONTACT_FIELDS.map((field) => (
          <ProfileFieldInput
            key={field.id}
            id={field.id}
            name={field.name}
            label={field.label}
            type={field.type}
            required={field.required}
            placeholder={field.placeholder}
            icon={field.icon}
            className={field.className}
            defaultValue={
              restored?.[field.profileKey] ?? contact[field.profileKey] ?? ""
            }
          />
        ))}
      </div>

      <div className="flex gap-2 justify-end">
        <Button
          type="button"
          variant="outline"
          size="sm"
          onClick={() => onSaved()}
          disabled={isPending}
        >
          {CONTACT_INFO_LABELS.cancel}
        </Button>

        <AccountSubmitButton
          label={CONTACT_INFO_LABELS.save}
          pendingLabel={CONTACT_INFO_LABELS.saving}
        />
      </div>
    </form>
  );
}
