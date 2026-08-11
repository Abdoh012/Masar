import Link from "next/link";

import Motion from "@/shared/components/animation/Motion";
import StaggerGroup from "@/shared/components/animation/StaggerGroup";
import { fadeInUp } from "@/shared/lib/animations";
import { Button } from "@/shared/components/ui/button";
import { HOME_CTA_BAND } from "./home-cta-band.content";

export function HomeCtaBand() {
  return (
    <section className="bg-primary py-20 sm:py-24">
      <StaggerGroup className="mx-auto flex w-full max-w-4xl flex-col items-center gap-6 px-6 text-center">
        {/* Section Title */}
        <Motion variants={fadeInUp}>
          <h2 className="font-heading max-w-2xl text-2xl font-semibold tracking-tight text-neutral-50 sm:text-3xl">
            {HOME_CTA_BAND.title}
          </h2>
        </Motion>

        {/* Section Description */}
        <Motion variants={fadeInUp}>
          <p className="max-w-xl leading-relaxed text-neutral-50/80">
            {HOME_CTA_BAND.subline}
          </p>
        </Motion>

        {/* Sign Up Button */}
        <Motion variants={fadeInUp}>
          <Button asChild size="lg" variant="accent" className="mt-2">
            <Link href="/sign-up">{HOME_CTA_BAND.cta}</Link>
          </Button>
        </Motion>
      </StaggerGroup>
    </section>
  );
}