import { BROWSE_HERO } from "./constants";

export function BrowseHero() {
  return (
    <section className="px-6 py-10 sm:px-10 relative">
      <p className="font-semibold uppercase text-secondary">
        {BROWSE_HERO.eyebrow}
      </p>

      <h1 className="mt-2 font-sans text-3xl font-semibold text-primary dark:text-destructive-foreground sm:text-4xl">
        {BROWSE_HERO.title}
      </h1>

      <p className="mt-3 max-w-2xl text-sm leading-relaxed text-primary dark:text-destructive-foreground">
        {BROWSE_HERO.subtitle}
      </p>

      {/* <span className="absolute w-[2px] h-30 bg-secondary top-1/2 left-5 -translate-y-1/2"></span> */}
    </section>
  );
}
