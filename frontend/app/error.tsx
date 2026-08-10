"use client";

import Link from "next/link";
import { useEffect } from "react";

import { CircleAlert } from "lucide-react";

import Motion from "@/shared/components/animation/components/Motion";
import { Button } from "@/shared/components/ui/button";
import { fadeInUp } from "@/shared/lib/animations";

export default function Error({
  error,
  reset,
}: {
  error: Error & { digest?: string };
  reset: () => void;
}) {
  useEffect(() => {
    console.error(error);
  }, [error]);

  return (
    <section
      role="alert"
      className="flex min-h-dvh flex-col items-center justify-center gap-4 bg-background px-6 font-sans text-center text-foreground"
    >
      <Motion
        variants={fadeInUp}
        initial="hidden"
        animate="visible"
        className="flex flex-col items-center gap-4"
      >
        <span className="grid size-12 place-items-center rounded-full bg-error-bg text-error-fg">
          <CircleAlert className="h-6 w-6" strokeWidth={2} />
        </span>
        <h1 className="text-2xl font-semibold text-primary-text">
          Something went wrong
        </h1>
        <p className="max-w-sm text-sm leading-relaxed text-muted-foreground">
          An unexpected error occurred. Please try again.
        </p>
        <div className="mt-2 flex flex-col items-stretch gap-2 sm:flex-row">
          <Button onClick={() => reset()}>Try again</Button>
          <Button asChild variant="outline">
            <Link href="/">Back home</Link>
          </Button>
        </div>
      </Motion>
    </section>
  );
}
