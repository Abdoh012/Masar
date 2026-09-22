import { Award } from "lucide-react";

import Motion from "@/shared/components/animation/Motion";
import { fadeInUp } from "@/shared/lib/animations";

import { HOW_IT_WORKS_STEPS, HOW_IT_WORKS_TITLE } from "./constants";
import { CertificateHowItWorksStep } from "./CertificateHowItWorksStep";

// CertificateHowItWorks: the intro panel that explains the certificate journey
// (the spec's eligibility chain, presented as three human steps). Purely
// presentational — draws a dashed connector rail behind the step icons on sm+
// (trimmed to run exactly between the outer circle edges at top-6, the center
// of the size-12 circles) and composes one numbered per-step leaf per entry in
// a responsive three-up grid. On mobile the rail hides and the steps stack.
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

      <div className="relative">
        {/* Dashed path from step 1's icon edge to step 3's icon edge: each icon
            circle is centered in its own 1/3 column, so the rail runs from
            16.666% + 1.5rem (the circle's radius) to the mirror on the right.
            Hidden on mobile where the steps stack vertically. */}
        <span
          aria-hidden
          className="absolute left-[calc(16.666%_+_1.5rem)] right-[calc(16.666%_+_1.5rem)] top-6 hidden border-t border-dashed border-border sm:block"
        />

        <ol className="mt-6 grid grid-cols-1 gap-10 sm:grid-cols-3 sm:gap-6">
          {HOW_IT_WORKS_STEPS.map((step, index) => (
            <CertificateHowItWorksStep key={step.title} step={step} index={index} />
          ))}
        </ol>
      </div>
    </Motion>
  );
}