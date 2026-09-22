import { Sparkles } from "lucide-react";

import Motion from "@/shared/components/animation/Motion";
import { containerVariants, fadeInUp } from "@/shared/lib/animations";

import { EligibleCertificateCard } from "./EligibleCertificateCard";
import { EligibleEmptyState } from "./EligibleEmptyState";
import { RequestConfirmDialog } from "./RequestConfirmDialog";

// EligibleSectionContainer: orchestrator for the "eligible to request" section.
// "use client" because it owns the eligible list state and the request dialog
// target. The list is seeded from the server-fetched eligible feed
// (initialEligible — no mock data); a request removes the training from this
// list (it re-appears as a pending record in the "Requested certificates"
// group — the page owns that transition via onRequested). Composes the
// per-training cards, the empty state, and the confirm dialog — no detailed
// markup of its own.
export function EligibleSectionContainer() {
  return (
    <Motion
      variants={fadeInUp}
      initial="hidden"
      whileInView="visible"
      viewport={{ once: true, margin: "-40px" }}
      transition={{ duration: 0.24, ease: "easeOut" }}
      className="space-y-4"
    >
      <div className="flex items-center gap-2">
        <span className="flex size-6 items-center justify-center rounded-full bg-secondary-tint text-secondary-text">
          <Sparkles className="size-3.5" />
        </span>
        <h2 className="text-base font-semibold text-primary-text">
          Eligible to request
        </h2>
      </div>

      {5 > 0 ? (
        <Motion
          variants={containerVariants}
          initial="hidden"
          whileInView="visible"
          viewport={{ once: true }}
          className="space-y-3"
        >
          <Motion variants={fadeInUp}>
            <EligibleCertificateCard />
          </Motion>
        </Motion>
      ) : (
        <EligibleEmptyState />
      )}

      {/* <RequestConfirmDialog
        training={requestTarget}
        onOpenChange={(open) => {
          if (!open) setRequestTarget(null);
        }}
        onConfirm={handleConfirm}
      /> */}
    </Motion>
  );
}
