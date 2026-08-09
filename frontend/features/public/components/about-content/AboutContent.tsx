import { PageIntro, SiteSection } from "../shared";
import { ABOUT_CONTENT } from "./about-content.content";

// AboutContent: About page — PageIntro followed by mission/who/values
// prose sections (US3).
export function AboutContent() {
  const { sections } = ABOUT_CONTENT;
  return (
    <section className="bg-background py-16 sm:py-20">
      <div className="mx-auto w-full max-w-4xl px-6">
        <PageIntro
          eyebrow={ABOUT_CONTENT.eyebrow}
          title={ABOUT_CONTENT.title}
          summary={ABOUT_CONTENT.summary}
        />
        <SiteSection sections={sections} />
      </div>
    </section>
  );
}