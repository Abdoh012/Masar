"use client";

import { AlertCircle } from "lucide-react";

import Motion from "@/shared/components/animation/Motion";
import { fadeInUp } from "@/shared/lib/animations";
import { Button } from "@/shared/components/ui/button";

// CertificatesErrorState: full-section error panel shown when the certificates
// data fails to load. Mirrors the root error-state language (circular error
// icon + heading + message + action) and offers a retry via the router.
export function CertificatesErrorState({ onRetry }: { onRetry: () => void }) {
  return (
    <Motion
      variants={fadeInUp}
      initial="hidden"
      whileInView="visible"
      viewport={{ once: true }}
      role="alert"
      className="flex flex-col items-center justify-center gap-3 rounded-2xl border border-border bg-card p-10 text-center"
    >
      <span className="flex size-12 items-center justify-center rounded-full bg-error-bg text-error-fg">
        <AlertCircle className="size-6" />
      </span>
      <p className="font-sans text-base font-semibold text-foreground">
        Couldn't load your certificates
      </p>
      <p className="max-w-sm text-sm text-muted-foreground">
        Something went wrong while fetching your certificate data. Please try
        again.
      </p>
      <div className="mt-2">
        <Button type="button" variant="outline" size="sm" onClick={onRetry}>
          Try again
        </Button>
      </div>
    </Motion>
  );
}
