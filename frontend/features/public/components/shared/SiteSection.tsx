import type { LegalSection } from "../../types";

interface SiteSectionProps {
  sections: LegalSection[];
}

// SiteSection: renders titled prose blocks on a prose-width container,
// used by the informational/legal pages. Each block is a section with an
// id (for anchors) and one or more paragraphs of readable body text.
export function SiteSection({ sections }: SiteSectionProps) {
  return (
    <div className="mx-auto w-full max-w-prose space-y-10">
      {sections.map((section) => (
        <section key={section.id} id={section.id} aria-labelledby={`${section.id}-title`}>
          <h2
            id={`${section.id}-title`}
            className="font-heading text-xl font-semibold tracking-tight text-primary-text"
          >
            {section.heading}
          </h2>
          <div className="mt-3 space-y-3">
            {section.paragraphs.map((paragraph, index) => (
              <p
                key={index}
                className="text-base leading-relaxed text-foreground/80"
              >
                {paragraph}
              </p>
            ))}
          </div>
        </section>
      ))}
    </div>
  );
}