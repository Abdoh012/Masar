import Link from "next/link";

import Motion from "@/shared/components/animation/Motion";
import { Button } from "@/shared/components/ui/button";
import { fadeInUp } from "@/shared/lib/animations";

export default function NotFound() {
  return (
    <section className="flex min-h-[40vh] flex-col items-center justify-center gap-4 bg-background px-6 font-sans text-center text-foreground">
      <Motion
        variants={fadeInUp}
        initial="hidden"
        animate="visible"
        className="flex flex-col items-center gap-4"
      >
        <span className="font-mono text-xs font-semibold uppercase tracking-[0.2em] text-secondary-text">
          404
        </span>
        <h1 className="text-2xl font-semibold text-primary-text">Listing not found</h1>
        <p className="max-w-sm text-sm leading-relaxed text-muted-foreground">
          This training listing doesn&apos;t exist or may have been removed.
        </p>
        <Button asChild className="mt-2">
          <Link href="/listings">Back to browse</Link>
        </Button>
      </Motion>
    </section>
  );
}
