// ContactForm copy (FR-015/025) — client-side field metadata + simulated
// success message; nothing persists (FR-026).
import type { ContactFormData } from "../../types";

export const CONTACT_FORM: ContactFormData = {
  fields: [
    {
      name: "name",
      label: "Your name",
      placeholder: "Jane Doe",
      required: true,
      type: "text",
    },
    {
      name: "email",
      label: "Email address",
      placeholder: "you@example.com",
      required: true,
      type: "email",
    },
    {
      name: "message",
      label: "How can we help?",
      placeholder: "Describe your question or issue…",
      required: true,
      type: "textarea",
    },
  ],
  submitLabel: "Send message",
  successTitle: "Message sent",
  successBody:
    "Thanks for reaching out. We've received your message and will reply to the email address you provided — usually within two business days.",
};