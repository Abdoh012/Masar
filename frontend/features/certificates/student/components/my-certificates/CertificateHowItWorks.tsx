import { Award } from "lucide-react";

import Motion from "@/shared/components/animation/Motion";
import { fadeInUp } from "@/shared/lib/animations";

import { HOW_IT_WORKS_STEPS, HOW_IT_WORKS_TITLE } from "./constants";
import { CertificateHowItWorksStep } from "./CertificateHowItWorksStep";

// CertificateHowItWorks: the intro panel that explains the certificate journey
// (the spec's eligibility chain, presented as three human steps). Purely
// presentational — composes a per-step leaf in a responsive column/grid.
export function CertificateHowItWorks() {
  return (
    <Motion
      variants={fadeInUp}
      initial="hidden"
      whileInView="visible"
      viewport={{ once: true, margin: "-40px" }}
      transition={{ duration: 0.24, ease: "easeOut" }}
      className="rounded-2xl border border-border bg-card p-6 shadow-card"
    >
      <h2 className="flex items-center gap-2 text-sm font-semibold uppercase tracking-wide text-secondary-text">
        <Award className="size-4" />
        {HOW_IT_WORKS_TITLE}
      </h2>

      <div className="mt-5 grid grid-cols-1 gap-6 sm:grid-cols-3">
        {HOW_IT_WORKS_STEPS.map((step) => (
          <CertificateHowItWorksStep key={step.title} step={step} />
        ))}
      </div>
    </Motion>
  );
}
