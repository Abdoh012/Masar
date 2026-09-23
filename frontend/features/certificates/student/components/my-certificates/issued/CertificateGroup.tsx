import { Ban, Check, Clock } from "lucide-react";

import Motion from "@/shared/components/animation/Motion";
import { containerVariants, fadeInUp } from "@/shared/lib/animations";
import { cn } from "@/shared/lib/utils";

import type { StudentCertificate } from "../../../types";
import { CertificateGroupEmpty } from "./CertificateGroupEmpty";
import { StudentCertificateCard } from "./StudentCertificateCard";

// Shared by CertificateGroup (heading) and CertificateGroupEmpty (panel icon).
export const GROUP_ICONS = {
  clock: Clock,
  check: Check,
  ban: Ban,
} as const;

export type CertificateGroupIcon = keyof typeof GROUP_ICONS;

export interface CertificateGroupConfig {
  title: string;
  icon: CertificateGroupIcon;
  accent: string;
  emptyTitle: string;
  emptyMessage: string;
}

interface CertificateGroupProps {
  config: CertificateGroupConfig;
  items: StudentCertificate[];
}

// CertificateGroup: one bucket inside the "Your certificates" section — the
// requested (still awaiting confirmation) bucket, the issued bucket, or the
// revoked bucket — each fed by its own server-fetched list
// (CertificateSectionContainer → student/api.ts). Renders its tinted heading
// and, per item, a StudentCertificateCard row inside a single grouped panel
// (one border/shadow, rows divided by hairlines — same pattern as the eligible
// section, §eligible); falls back to its own empty state when the bucket is
// empty.
export function CertificateGroup({ config, items }: CertificateGroupProps) {
  const Icon = GROUP_ICONS[config.icon];

  return (
    <div className="space-y-3">
      <div className="flex items-center gap-2">
        <span className={cn("flex size-6 items-center justify-center rounded-full", config.accent)}>
          <Icon className="size-3.5" />
        </span>
        <h3 className="text-sm font-semibold text-primary-text">{config.title}</h3>
      </div>

      {items.length > 0 ? (
        <Motion
          variants={containerVariants}
          initial="hidden"
          whileInView="visible"
          viewport={{ once: true }}
          className="divide-y divide-border overflow-hidden rounded-xl border border-border bg-card shadow-card"
        >
          {items.map((certificate) => (
            <Motion key={certificate.id} variants={fadeInUp}>
              <StudentCertificateCard certificate={certificate} />
            </Motion>
          ))}
        </Motion>
      ) : (
        <CertificateGroupEmpty config={config} />
      )}
    </div>
  );
}