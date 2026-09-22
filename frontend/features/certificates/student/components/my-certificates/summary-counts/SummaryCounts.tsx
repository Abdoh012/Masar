import { BadgeCheck, CheckCircle2, Clock } from "lucide-react";

import Motion from "@/shared/components/animation/Motion";
import { fadeInUp } from "@/shared/lib/animations";

import type { CertificateCounts } from "../../../types";
import { SUMMARY_CARD_META } from "../constants";
import { SummaryCard } from "./SummaryCard";

const SUMMARY_CARD_ICONS = {
  "badge-check": BadgeCheck,
  clock: Clock,
  "check-circle": CheckCircle2,
} as const;

// SummaryCounts: the three lifecycle stat cards (Eligible to request / In
// review / Issued) in a responsive three-up row that stacks on mobile. Each
// card wears its lifecycle state's semantic tint from SUMMARY_CARD_META (info
// blue, warning amber, success green — all existing tokens) and composes one
// SummaryCard leaf per count. Pure presentation — counts arrive as props.
export function SummaryCounts({ counts }: { counts: CertificateCounts }) {
  return (
    <Motion
      variants={fadeInUp}
      initial="hidden"
      whileInView="visible"
      viewport={{ once: true, margin: "-40px" }}
      transition={{ duration: 0.24, ease: "easeOut" }}
      className="grid grid-cols-1 gap-3 sm:grid-cols-3"
    >
      {SUMMARY_CARD_META.map((meta) => {
        const Icon = SUMMARY_CARD_ICONS[meta.icon];
        return (
          <SummaryCard
            key={meta.label}
            label={meta.label}
            value={counts[meta.key]}
            cardTint={meta.cardTint}
            iconTint={meta.iconTint}
            icon={<Icon className="size-5" />}
          />
        );
      })}
    </Motion>
  );
}