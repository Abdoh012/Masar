// Section-level types for support-content (structure rules §14).

export interface FaqItem {
  question: string;
  answer: string;
}

export interface SupportContactData {
  email: string;
  channelLabel: string;
  channelValue: string;
  responseTime: string;
}

export interface ContactFormField {
  name: "name" | "email" | "message";
  label: string;
  placeholder: string;
  required: boolean;
  type: "text" | "email" | "textarea";
}

export interface ContactFormData {
  fields: ContactFormField[];
  submitLabel: string;
  successTitle: string;
  successBody: string;
}