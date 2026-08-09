import { PageIntro, SiteSection } from "../shared";
import { TERMS_CONTENT } from "./terms-content.content";

// TermsContent: terms of service — PageIntro, last-updated line
// (FR-013), then legal sections.
export function TermsContent() {
  const { sections } = TERMS_CONTENT;
  return (
    <section className="bg-background py-16 sm:py-20">
      <div className="mx-auto w-full max-w-4xl px-6">
        <PageIntro
          eyebrow={TERMS_CONTENT.eyebrow}
          title={TERMS_CONTENT.title}
          summary={TERMS_CONTENT.summary}
        />
        {TERMS_CONTENT.lastUpdated ? (
          <p className="mb-10 text-center font-mono text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">
            Last updated: {TERMS_CONTENT.lastUpdated}
          </p>
        ) : null}
        <SiteSection sections={sections} />
      </div>
    </section>
  );
}