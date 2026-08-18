import { BROWSE_HERO } from "./constants";

// BrowseHero: the navy page header band for the student browse surface
// (eyebrow / title / subtitle). Pure presentational leaf — copy lives in the
// sibling constants file, no state, no fetching.
export function BrowseHero() {
  return (
    <section className="bg-primary px-6 py-10 sm:px-10 relative">
      <p className="font-semibold uppercase text-secondary">
        {BROWSE_HERO.eyebrow}
      </p>
      <h1 className="mt-2 font-sans text-3xl font-semibold text-neutral-50 sm:text-4xl">
        {BROWSE_HERO.title}
      </h1>
      <p className="mt-3 max-w-2xl text-sm leading-relaxed text-neutral-300">
        {BROWSE_HERO.subtitle}
      </p>

      <span className="absolute w-[2px] h-30 bg-secondary top-1/2 left-5 -translate-y-1/2"></span>
    </section>
  );
}