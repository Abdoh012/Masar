import { GraduationCap } from "lucide-react";

import Motion from "@/shared/components/animation/Motion";
import { fadeInUp } from "@/shared/lib/animations";
import { Button } from "@/shared/components/ui/button";
import Link from "next/link";

import { EMPTY_COPY } from "../constants";

// EligibleEmptyState: dashed "nothing eligible yet" panel with a browse CTA,
// shown when the student has no completed trainings to request a certificate
// from. Leaf — renders only its copy.
export function EligibleEmptyState() {
  return (
    <Motion
      variants={fadeInUp}
      initial="hidden"
      whileInView="visible"
      viewport={{ once: true }}
      className="flex flex-col items-center gap-2 rounded-xl border border-dashed border-border px-6 py-10 text-center"
    >
      <span className="flex size-11 items-center justify-center rounded-full bg-primary-tint text-primary-text">
        <GraduationCap className="size-5" />
      </span>
      <p className="font-sans text-base font-semibold text-foreground">
        No trainings eligible yet
      </p>
      <p className="max-w-sm text-sm text-muted-foreground">
        Complete a training and it will appear here, ready for a certificate.
      </p>
      <div className="mt-3">
        <Button asChild size="sm" variant="outline">
          <Link href={EMPTY_COPY.ctaHref}>{EMPTY_COPY.ctaLabel}</Link>
        </Button>
      </div>
    </Motion>
  );
}
