import { HOME_HOW_IT_WORKS } from "./home-how-it-works.content";

// StepCard: numbered how-it-works step.
function StepCard({
  step,
  title,
  description,
}: {
  step: number;
  title: string;
  description: string;
}) {
  return (
    <li className="flex flex-col gap-3">
      <span className="font-mono text-sm font-semibold text-secondary-text">
        Step {step}
      </span>
      <h3 className="font-heading text-lg font-semibold tracking-tight text-primary-text">
        {title}
      </h3>
      <p className="leading-relaxed text-muted-foreground">{description}</p>
    </li>
  );
}

// HomeHowItWorks: the three-step flow on the landing page. The section
// carrier MUST carry id="how-it-works" (FR-009) for the hero anchor.
export function HomeHowItWorks() {
  return (
    <section
      id="how-it-works"
      className="bg-card py-20 sm:py-24"
      aria-labelledby="how-it-works-title"
    >
      <div className="mx-auto w-full max-w-6xl px-6">
        <div className="mx-auto max-w-2xl text-center">
          <p className="font-mono text-xs font-semibold uppercase tracking-[0.2em] text-secondary-text">
            How it works
          </p>
          <h2
            id="how-it-works-title"
            className="font-heading mt-3 text-2xl font-semibold tracking-tight text-primary-text sm:text-3xl"
          >
            Three steps from student to opportunity
          </h2>
        </div>
        <ol className="mt-12 grid gap-10 sm:grid-cols-3 sm:gap-6">
          {HOME_HOW_IT_WORKS.map((step) => (
            <StepCard key={step.step} {...step} />
          ))}
        </ol>
      </div>
    </section>
  );
}