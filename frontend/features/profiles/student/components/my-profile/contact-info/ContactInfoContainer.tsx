"use client";

import { useState } from "react";
import { Contact, Globe, Link2, Mail, MapPin, Phone } from "lucide-react";

import { Button } from "@/shared/components/ui/button";

import { ProfileFieldInput } from "../ProfileFieldInput";
import { ProfileSection } from "../ProfileSection";
import { INITIAL_CONTACT } from "../constants";
import { CONTACT_INFO_LABELS } from "./constants";
import type { ContactInfo } from "../../../types";

// ContactInfoContainer: the five contact fields, each with its own leading icon
// so the block is scannable. The fields are written out individually (rather
// than mapped from a config array) because each one picks its own icon and
// input type. State is local and seeded from constants; the form's submit is
// inert until the contact API exists.
export default function ContactInfoContainer() {
  const [contact, setContact] = useState<ContactInfo>(INITIAL_CONTACT);

  const setField = (field: keyof ContactInfo) => (value: string) => {
    setContact((current) => ({ ...current, [field]: value }));
  };

  return (
    <ProfileSection
      icon={Contact}
      title={CONTACT_INFO_LABELS.title}
      description={CONTACT_INFO_LABELS.description}
    >
      <form
        className="space-y-4"
        onSubmit={(event) => {
          // TODO: connect to backend — hand this to the updateContactInfo server
          // action (student/actions.ts), then revalidatePath("/profile").
          event.preventDefault();
        }}
      >
        <div className="grid gap-4 sm:grid-cols-2">
          <ProfileFieldInput
            id="contact-email"
            label={CONTACT_INFO_LABELS.fields.email}
            type="email"
            icon={Mail}
            value={contact.email}
            onChange={setField("email")}
            placeholder={CONTACT_INFO_LABELS.placeholders.email}
          />

          <ProfileFieldInput
            id="contact-phone"
            label={CONTACT_INFO_LABELS.fields.phone}
            type="tel"
            icon={Phone}
            value={contact.phone}
            onChange={setField("phone")}
            placeholder={CONTACT_INFO_LABELS.placeholders.phone}
          />

          <ProfileFieldInput
            id="contact-website"
            label={CONTACT_INFO_LABELS.fields.website}
            icon={Globe}
            value={contact.website}
            onChange={setField("website")}
            placeholder={CONTACT_INFO_LABELS.placeholders.website}
          />

          <ProfileFieldInput
            id="contact-profile-link"
            label={CONTACT_INFO_LABELS.fields.profileLink}
            type="url"
            icon={Link2}
            value={contact.profileLink}
            onChange={setField("profileLink")}
            placeholder={CONTACT_INFO_LABELS.placeholders.profileLink}
          />

          <ProfileFieldInput
            id="contact-address"
            label={CONTACT_INFO_LABELS.fields.address}
            icon={MapPin}
            value={contact.address}
            onChange={setField("address")}
            placeholder={CONTACT_INFO_LABELS.placeholders.address}
            className="sm:col-span-2"
          />
        </div>

        <div className="flex justify-end">
          <Button type="submit" size="sm" className="cursor-pointer">
            {CONTACT_INFO_LABELS.save}
          </Button>
        </div>
      </form>
    </ProfileSection>
  );
}
