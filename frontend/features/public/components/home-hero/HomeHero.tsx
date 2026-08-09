import Link from "next/link";

import { Button } from "@/shared/components/ui/button";
import { HOME_HERO } from "./home-hero.content";

// HomeHero: the landing page hero — headline, subline, primary CTA to
// sign-up and a secondary in-page anchor to the how-it-works section.
export function HomeHero() {
  return (
    <section
      id="hero"
      className="relative overflow-hidden bg-primary text-neutral-50"
      aria-labelledby="hero-title"
    >
      <div className="mx-auto flex w-full max-w-6xl flex-col items-center gap-6 px-6 py-20 text-center sm:py-28">
        <p className="font-mono text-xs font-semibold uppercase tracking-[0.2em] text-secondary-500">
          Masar
        </p>
        <h1
          id="hero-title"
          className="font-heading max-w-3xl text-3xl font-semibold leading-tight tracking-tight sm:text-4xl lg:text-5xl"
        >
          {HOME_HERO.title}
        </h1>
        <p className="max-w-2xl text-base leading-relaxed text-neutral-50/80 sm:text-lg">
          {HOME_HERO.subline}
        </p>
        <div className="mt-2 flex flex-col items-center gap-3 sm:flex-row">
          <Button asChild variant="accent" size="lg">
            <Link href="/sign-up">{HOME_HERO.primaryCta}</Link>
          </Button>
          <Button
            asChild
            variant="ghost"
            size="lg"
            className="border border-neutral-50/30 text-neutral-50 hover:bg-primary-tint hover:text-primary"
          >
            <Link href="#how-it-works">{HOME_HERO.secondaryCta}</Link>
          </Button>
        </div>
      </div>
    </section>
  );
}
