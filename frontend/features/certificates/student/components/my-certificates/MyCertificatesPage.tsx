"use client";

import { useEffect, useState } from "react";
import { Award } from "lucide-react";

import Motion from "@/shared/components/animation/Motion";
import { fadeInUp } from "@/shared/lib/animations";

import type { EligibleTraining, StudentCertificate } from "../../types";
import { CertificateCounts } from "../../types";
import { showSuccess } from "@/shared/lib/notifications";
import { CertificateHowItWorks } from "./CertificateHowItWorks";
import { CertificatesErrorState } from "./CertificatesErrorState";
import { CertificatesPageSkeleton } from "./CertificatesPageSkeleton";
import {
  buildPendingCertificate,
  MOCK_CERTIFICATES,
  MOCK_COUNTS,
  PAGE_LABELS,
  REQUEST_TOAST_COPY,
  SUMMARY_LABELS,
} from "./constants";
import { EligibleSectionContainer } from "./eligible/EligibleSectionContainer";
import { CertificateSectionContainer } from "./issued/CertificateSectionContainer";

// Demo switch (UI-only). Flip to preview the different page states without
// wiring a backend: "content" (default, fully populated), "loading",
// "empty", "error".
export type CertificatesPageDemoMode = "content" | "loading" | "empty" | "error";

// MyCertificatesPage: orchestrator for the /certificates page. "use client"
// because it owns the demo state switch. Renders the page header + summary
// counts, the how-it-works panel, the eligible-to-request list, and the
// certificate records (requested / issued). Owns the records list state so a
// confirmed request moves the training out of the eligible section and in here
// as a pending record; fires the request-success toast.
export function MyCertificatesPage({
  demoMode = "content",
}: {
  demoMode?: CertificatesPageDemoMode;
}) {
  const [mode, setMode] = useState<CertificatesPageDemoMode>(demoMode);

  // Simulate a loading pass on first mount so the skeleton is demonstrable,
  // then settle on the requested mode. Each retry repeats it.
  const [booted, setBooted] = useState(false);
  const [certificates, setCertificates] = useState<StudentCertificate[]>(
    MOCK_CERTIFICATES,
  );
  const [counts, setCounts] = useState<CertificateCounts>(MOCK_COUNTS);

  useEffect(() => {
    const id = setTimeout(() => setBooted(true), 650);
    return () => clearTimeout(id);
  }, [mode]);

  const handleRequested = (training: EligibleTraining) => {
    setCertificates((current) => [...current, buildPendingCertificate(training)]);
    setCounts((c) => ({
      eligible: Math.max(0, c.eligible - 1),
      pending: c.pending + 1,
      issued: c.issued,
    }));
    showSuccess(REQUEST_TOAST_COPY.message(training.companyName));
  };

  const handleRetry = () => {
    setBooted(false);
    setMode("content");
  };

  if (mode === "loading" || !booted) {
    return <CertificatesPageSkeleton />;
  }

  if (mode === "error") {
    return <CertificatesErrorState onRetry={handleRetry} />;
  }

  const displayCertificates = mode === "empty" ? [] : certificates;
  const displayCounts = mode === "empty" ? { eligible: 0, pending: 0, issued: 0 } : counts;

  return (
    <div className="space-y-8">
      {/* Header */}
      <Motion
        variants={fadeInUp}
        initial="hidden"
        whileInView="visible"
        viewport={{ once: true }}
        className="flex flex-wrap items-end justify-between gap-3"
      >
        <div className="space-y-1.5">
          <p className="font-mono text-xs font-semibold uppercase tracking-[0.18em] text-secondary-text">
            {PAGE_LABELS.eyebrow}
          </p>
          <h1 className="font-sans text-2xl font-semibold text-primary-text">
            {PAGE_LABELS.title}
          </h1>
          <p className="max-w-xl text-sm text-muted-foreground">
            {PAGE_LABELS.description}
          </p>
        </div>
        <div className="flex items-center gap-1.5 rounded-full bg-primary-tint px-3 py-1.5 text-sm font-semibold text-primary-text">
          <Award className="size-4" />
          {displayCounts.issued} issued
        </div>
      </Motion>

      {/* Summary counts */}
      <SummaryCounts counts={displayCounts} />

      {/* How it works */}
      {mode !== "empty" ? <CertificateHowItWorks /> : null}

      {/* Eligible to request */}
      {mode !== "empty" ? (
        <EligibleSectionContainer onRequested={handleRequested} />
      ) : null}

      {/* Your certificates */}
      <CertificateSectionContainer certificates={displayCertificates} />
    </div>
  );
}

// --- Counts strip: eligible / in-review / issued summary cards ---

function SummaryCounts({ counts }: { counts: CertificateCounts }) {
  const rows = [
    { label: SUMMARY_LABELS.eligible, value: counts.eligible, accent: "bg-secondary-tint text-secondary-text" },
    { label: SUMMARY_LABELS.pending, value: counts.pending, accent: "bg-warning-bg text-warning-fg" },
    { label: SUMMARY_LABELS.issued, value: counts.issued, accent: "bg-success-bg text-success-fg" },
  ];

  return (
    <Motion
      variants={fadeInUp}
      initial="hidden"
      whileInView="visible"
      viewport={{ once: true, margin: "-40px" }}
      transition={{ duration: 0.24, ease: "easeOut" }}
      className="grid grid-cols-1 gap-3 sm:grid-cols-3"
    >
      {rows.map((row) => (
        <div
          key={row.label}
          className="flex items-center justify-between rounded-2xl border border-border bg-card p-4 shadow-card"
        >
          <span className="text-sm font-medium text-muted-foreground">{row.label}</span>
          <span
            className={`flex size-8 items-center justify-center rounded-full font-sans text-base font-semibold ${row.accent}`}
          >
            {row.value}
          </span>
        </div>
      ))}
    </Motion>
  );
}
