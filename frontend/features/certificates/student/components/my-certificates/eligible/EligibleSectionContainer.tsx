import { Sparkles } from "lucide-react";

import Motion from "@/shared/components/animation/Motion";
import { containerVariants, fadeInUp } from "@/shared/lib/animations";

import type { EligibleTraining } from "../../../types";
import { EligibleCertificateCard } from "./EligibleCertificateCard";
import { EligibleEmptyState } from "./EligibleEmptyState";

interface EligibleSectionContainerProps {
  items: EligibleTraining[];
}

// EligibleSectionContainer: server orchestrator for the "eligible to request"
// section. Receives the server-fetched eligible feed (CertificatePageContent →
// fetchEligibleFeed → normalizeEligibleTrainings) and renders one
// EligibleCertificateCard row per training inside a single grouped panel
// (one border/shadow, rows divided by hairlines), or the EligibleEmptyState
// when there are none. A confirmed request removes the training server-side
// (the action revalidates /certificates and the backend stops listing it once
// a row exists) — the client keeps no list state. Composition only; per-card
// interactivity lives in the client EligibleRequestAction leaf.
export function EligibleSectionContainer({
  items,
}: EligibleSectionContainerProps) {
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

      {items.length > 0 ? (
        <Motion
          variants={containerVariants}
          initial="hidden"
          whileInView="visible"
          viewport={{ once: true }}
          className="divide-y divide-border overflow-hidden rounded-xl border border-border bg-card shadow-card"
        >
          {items.map((training) => (
            <Motion key={training.id} variants={fadeInUp}>
              <EligibleCertificateCard training={training} />
            </Motion>
          ))}
        </Motion>
      ) : (
        <EligibleEmptyState />
      )}
    </Motion>
  );
}