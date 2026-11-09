// Copy for the contact-info section (structure rules §14).

import { Mail, MapPin, Phone, Globe, Link2 } from "lucide-react";
import type { LucideIcon } from "lucide-react";

import type { ContactInfo } from "../../../types";

export const CONTACT_INFO_LABELS = {
  title: "Contact info",
  description:
    "How companies reach you about a training you applied for. Only the email and phone are shown to companies you apply to.",
  save: "Save contact info",
  edit: "Edit contact info",
  cancel: "Cancel",
  saving: "Saving…",
  emptyNote: "No contact info set yet.",
} as const;

// Field config for the contact form. Each entry renders one ProfileFieldInput.
// The name is the FormData/backend field name (profile_link for the external
// profile link), while profileKey is the key the value lives under on the UI
// model (ContactInfo has no "profile_link" key, it has "profileLink").
// The className is optional — only the address field spans both grid columns.
// required is optional too — only the email (the account address) can't be cleared.
export type ContactField = {
  id: string;
  name: string;
  profileKey: keyof ContactInfo;
  label: string;
  type: "email" | "tel" | "url" | "text";
  icon: LucideIcon;
  placeholder: string;
  className?: string;
  required?: boolean;
};

export const CONTACT_FIELDS: ReadonlyArray<ContactField> = [
  {
    id: "contact-email",
    name: "email",
    profileKey: "email",
    label: "Email",
    type: "email",
    icon: Mail,
    placeholder: "you@example.com",
    required: true,
  },
  {
    id: "contact-phone",
    name: "phone",
    profileKey: "phone",
    label: "Phone number",
    type: "tel",
    icon: Phone,
    placeholder: "+20 100 000 0000",
  },
  {
    id: "contact-website",
    name: "website",
    profileKey: "website",
    label: "Website or portfolio",
    type: "url",
    icon: Globe,
    placeholder: "yoursite.com",
  },
  {
    id: "contact-profile-link",
    name: "profile_link",
    profileKey: "profileLink",
    label: "External profile",
    type: "url",
    icon: Link2,
    placeholder: "https://linkedin.com/in/you",
  },
  {
    id: "contact-address",
    name: "address",
    profileKey: "address",
    label: "Address",
    type: "text",
    icon: MapPin,
    placeholder: "Cairo, Egypt",
    className: "sm:col-span-2",
  },
];
