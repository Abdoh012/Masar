// Role-level types for the public feature (structure rules §14).
// The public feature has no role subfolders, so all its content contracts
// live in this single feature-level types.ts. See specs/002-public-pages/data-model.md.

// --- Shared across sections -------------------------------------------------

export interface NavLink {
  label: string;
  href: string;
}

export interface FooterColumn {
  title: string;
  links: NavLink[];
}

export interface PageIntroData {
  eyebrow: string;
  title: string;
  summary?: string;
}

export interface LegalSection {
  id: string;
  heading: string;
  paragraphs: string[];
}

export interface LegalDocument {
  eyebrow: string;
  title: string;
  summary: string;
  lastUpdated?: string;
  sections: LegalSection[];
}

// --- Section contracts (home-cta-band, home-features, ...) -------------------

export interface CtaBandData {
  title: string;
  subline: string;
  cta: string;
}

export interface FeatureProp {
  icon: string;
  title: string;
  description: string;
}

export interface HomeHeroData {
  eyebrow?: string;
  title: string;
  subline: string;
  primaryCta: string;
  secondaryCta: string;
}

export interface HowItWorksStep {
  step: number;
  title: string;
  description: string;
}

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