import { PageIntro, SiteSection } from "../shared";
import { PRIVACY_CONTENT } from "./privacy-content.content";

export function PrivacyContent() {
  const { sections } = PRIVACY_CONTENT;
  return (
    <section className="bg-background py-16 sm:py-20">
      <div className="mx-auto w-full max-w-4xl px-6">
        <PageIntro
          eyebrow={PRIVACY_CONTENT.eyebrow}
          title={PRIVACY_CONTENT.title}
          summary={PRIVACY_CONTENT.summary}
        />

        {/* PrivacyContent: privacy policy — PageIntro, last-updated line
        (FR-013), then legal sections. */}
        {PRIVACY_CONTENT.lastUpdated ? (
          <p className="mb-10 text-center font-mono text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">
            Last updated: {PRIVACY_CONTENT.lastUpdated}
          </p>
        ) : null}

        {/* SiteSection: legal sections */}
        <SiteSection sections={sections} />
      </div>
    </section>
  );
}
