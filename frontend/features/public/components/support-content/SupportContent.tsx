import { PageIntro } from "../shared";
import { ContactForm } from "./ContactForm";
import { FaqList } from "./FaqList";
import { SupportContactSection } from "./SupportContactSection";
import { SUPPORT_FAQ, SUPPORT_INTRO } from "./support-content.content";

// SupportContent: Support page — PageIntro, FAQ accordions, contact
// info, and the interactive ContactForm (US4).
export function SupportContent() {
  return (
    <section className="bg-background py-16 sm:py-20">
      <div className="mx-auto flex w-full max-w-4xl flex-col items-center gap-14 px-6">
        <PageIntro
          eyebrow={SUPPORT_INTRO.eyebrow}
          title={SUPPORT_INTRO.title}
          summary={SUPPORT_INTRO.summary}
        />

        {/* FAQ section - list of questions */}
        <div className="flex w-full flex-col gap-5">
          <h2 className="font-heading text-xl font-semibold text-primary-text">
            Frequently asked questions
          </h2>
          <FaqList items={SUPPORT_FAQ} />
        </div>

        {/* Contact section - contact info and form */}
        <div className="flex w-full flex-col gap-5">
          <h2 className="font-heading text-xl font-semibold text-primary-text">
            Contact us
          </h2>
          <SupportContactSection />
          <ContactForm />
        </div>
      </div>
    </section>
  );
}
