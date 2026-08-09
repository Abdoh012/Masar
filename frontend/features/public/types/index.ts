// Feature-level content contracts for the public feature (structure rules
// §14 — types used by 2+ sections live here; single-section types live in
// that section's own types/ folder). See specs/002-public-pages/data-model.md.

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