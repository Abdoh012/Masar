import type { PageIntroData } from "../../types";

// PageIntro: the single h1 page hero used at the top of the
// informational/legal/support pages — eyebrow + title + optional summary.
export function PageIntro({ eyebrow, title, summary }: PageIntroData) {
  return (
    <header className="flex flex-col items-center gap-4 pb-10 text-center sm:pb-14">
      <p className="font-mono text-xs font-semibold uppercase tracking-[0.2em] text-secondary-text">
        {eyebrow}
      </p>
      <h1 className="font-heading text-3xl font-semibold tracking-tight text-primary-text sm:text-4xl">
        {title}
      </h1>
      {summary ? (
        <p className="max-w-2xl text-base leading-relaxed text-muted-foreground sm:text-lg">
          {summary}
        </p>
      ) : null}
    </header>
  );
}