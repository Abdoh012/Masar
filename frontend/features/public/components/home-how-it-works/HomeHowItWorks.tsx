import Header from "@/features/public/components/shared/Header";
import Title from "@/features/public/components/shared/Title";
import Motion from "@/shared/components/animation/Motion";
import StaggerGroup from "@/shared/components/animation/StaggerGroup";
import { fadeInUp } from "@/shared/lib/animations";

import { HOME_HOW_IT_WORKS } from "./home-how-it-works.content";

interface StepCardProps {
  step: number;
  title: string;
  description: string;
}

// StepCard: one step in the flow. The <li> wrapper is provided by the
// animating Motion child (as="li"); this leaf renders only the card content.
function StepCard({ step, title, description }: StepCardProps) {
  return (
    <div className="flex flex-col gap-3">
      {/* Step number */}
      <span className="font-mono text-sm font-semibold text-secondary-text">
        Step {step}
      </span>

      {/* Step title */}
      <h3 className="font-heading text-lg font-semibold tracking-tight text-primary-text">
        {title}
      </h3>

      {/* Step description */}
      <p className="leading-relaxed text-muted-foreground">{description}</p>
    </div>
  );
}

// HomeHowItWorks: the three-step flow on the landing page. The section
// carrier MUST carry id="how-it-works" (FR-009) for the hero anchor.
// Heading pair and each step reveal individually, in sequence.
export function HomeHowItWorks() {
  return (
    <section className="bg-card py-20 sm:py-24">
      <div className="mx-auto w-full max-w-6xl px-6">
        {/* Section Heading + Title — animate individually */}
        <StaggerGroup className="mx-auto max-w-2xl text-center">
          <Motion variants={fadeInUp}>
            <Header title="How it works" />
          </Motion>

          <Motion variants={fadeInUp}>
            <Title title="Three steps from student to opportunity" />
          </Motion>
        </StaggerGroup>

        {/* Steps — the <ol> is the stagger container; each step is a list item */}
        <StaggerGroup as="ol" className="mt-12 grid gap-10 sm:grid-cols-3 sm:gap-6">
          {HOME_HOW_IT_WORKS.map((step) => (
            <Motion key={step.step} as="li" variants={fadeInUp}>
              <StepCard {...step} />
            </Motion>
          ))}
        </StaggerGroup>
      </div>
    </section>
  );
}