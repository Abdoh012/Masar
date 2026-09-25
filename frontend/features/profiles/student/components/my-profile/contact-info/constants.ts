// Copy for the contact-info section (structure rules §14).

export const CONTACT_INFO_LABELS = {
  title: "Contact info",
  description:
    "How companies reach you about a training you applied for. Only the email and phone are shown to companies you apply to.",
  save: "Save contact info",
  fields: {
    email: "Email",
    phone: "Phone number",
    website: "Website or portfolio",
    address: "Address",
    profileLink: "External profile",
  },
  placeholders: {
    email: "you@example.com",
    phone: "+20 100 000 0000",
    website: "yoursite.com",
    address: "Cairo, Egypt",
    profileLink: "https://linkedin.com/in/you",
  },
} as const;
