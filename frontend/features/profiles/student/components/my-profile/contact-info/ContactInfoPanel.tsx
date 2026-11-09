"use client";

import { useState } from "react";
import { Contact, Edit2 } from "lucide-react";

import { Button } from "@/shared/components/ui/button";

import { ContactFieldRow } from "./ContactFieldRow";
import { ContactInfoForm } from "./ContactInfoForm";
import { CONTACT_INFO_LABELS, CONTACT_FIELDS } from "./constants";
import { ProfileSection } from "../ProfileSection";
import type { ContactInfo } from "../../../types";

interface ContactInfoPanelProps {
  contact: ContactInfo;
}

export function ContactInfoPanel({ contact }: ContactInfoPanelProps) {
  const [isEditing, setIsEditing] = useState(false);

  const hasAnyValue =
    contact.email ||
    contact.phone ||
    contact.website ||
    contact.profileLink ||
    contact.address;

  return (
    <ProfileSection
      icon={<Contact className="size-4" />}
      title={CONTACT_INFO_LABELS.title}
      description={CONTACT_INFO_LABELS.description}
    >
      {isEditing ? (
        <ContactInfoForm contact={contact} onSaved={() => setIsEditing(false)} />
      ) : hasAnyValue ? (
        <div className="grid gap-4 sm:grid-cols-2">
          {CONTACT_FIELDS.map((field) => (
            <ContactFieldRow
              key={field.id}
              icon={field.icon}
              label={field.label}
              value={contact[field.profileKey]}
              className={field.className}
            />
          ))}
        </div>
      ) : (
        <p className="text-sm text-muted-foreground">
          {CONTACT_INFO_LABELS.emptyNote}
        </p>
      )}

      {!isEditing && hasAnyValue && (
        <div className="mt-6 flex justify-end">
          <Button
            type="button"
            variant="outline"
            size="sm"
            onClick={() => setIsEditing(true)}
          >
            {CONTACT_INFO_LABELS.edit}
          </Button>
        </div>
      )}
    </ProfileSection>
  );
}

