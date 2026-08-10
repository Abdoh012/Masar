import Header from "@/features/public/components/shared/Header";
import Title from "@/features/public/components/shared/Title";

import { HOME_HOW_IT_WORKS } from "./home-how-it-works.content";

interface StepCardProps {
  step: number;
  title: string;
  description: string;
}

function StepCard({ step, title, description }: StepCardProps) {
  return (
    <li className="flex flex-col gap-3">
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
    </li>
  );
}

// HomeHowItWorks: the three-step flow on the landing page. The section
// carrier MUST carry id="how-it-works" (FR-009) for the hero anchor.
export function HomeHowItWorks() {
  return (
    <section className="bg-card py-20 sm:py-24">
      <div className="mx-auto w-full max-w-6xl px-6">
        <div className="mx-auto max-w-2xl text-center">
          {/* Section Heading */}
          <Header title="How it works" />

          {/* Section Title */}
          <Title title="Three steps from student to opportunity" />
        </div>

        {/* Steps */}
        <ol className="mt-12 grid gap-10 sm:grid-cols-3 sm:gap-6">
          {HOME_HOW_IT_WORKS.map((step) => (
            <StepCard key={step.step} {...step} />
          ))}
        </ol>
      </div>
    </section>
  );
}
