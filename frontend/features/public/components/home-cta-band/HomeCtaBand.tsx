import Link from "next/link";

import { Button } from "@/shared/components/ui/button";
import { HOME_CTA_BAND } from "./home-cta-band.content";

// HomeCtaBand: closing call-to-action band ending the landing story.
export function HomeCtaBand() {
  return (
    <section className="bg-primary py-20 sm:py-24" aria-labelledby="cta-band-title">
      <div className="mx-auto flex w-full max-w-4xl flex-col items-center gap-6 px-6 text-center">
        <h2
          id="cta-band-title"
          className="font-heading max-w-2xl text-2xl font-semibold tracking-tight text-neutral-50 sm:text-3xl"
        >
          {HOME_CTA_BAND.title}
        </h2>
        <p className="max-w-xl leading-relaxed text-neutral-50/80">
          {HOME_CTA_BAND.subline}
        </p>
        <Button asChild size="lg" className="mt-2">
          <Link href="/sign-up">{HOME_CTA_BAND.cta}</Link>
        </Button>
      </div>
    </section>
  );
}