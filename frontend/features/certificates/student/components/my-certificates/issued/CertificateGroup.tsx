import { Check, Clock } from "lucide-react";

import Motion from "@/shared/components/animation/Motion";
import { fadeInUp } from "@/shared/lib/animations";
import { cn } from "@/shared/lib/utils";

import type { StudentCertificate } from "../../../types";
import { CertificateGroupEmpty } from "./CertificateGroupEmpty";
import { StudentCertificateCard } from "./StudentCertificateCard";

// Shared by CertificateGroup (heading) and CertificateGroupEmpty (panel icon).
export const GROUP_ICONS = {
  clock: Clock,
  check: Check,
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
  certificates: StudentCertificate[];
  onViewDetail: (certificate: StudentCertificate) => void;
}

// CertificateGroup: one bucket inside the "Your certificates" section — the
// requested (still awaiting confirmation) bucket or the issued/terminal
// bucket. Renders its tinted heading and, per item, a StudentCertificateCard;
// falls back to its own empty state when the bucket is empty. Leaf — maps
// over items, owns no data or state.
export function CertificateGroup({ config, certificates, onViewDetail }: CertificateGroupProps) {
  const Icon = GROUP_ICONS[config.icon];

  return (
    <div className="space-y-3">
      <div className="flex items-center gap-2">
        <span className={cn("flex size-6 items-center justify-center rounded-full", config.accent)}>
          <Icon className="size-3.5" />
        </span>
        <h3 className="text-sm font-semibold text-primary-text">{config.title}</h3>
      </div>

      {certificates.length > 0 ? (
        // Each card drives its own entrance: a container-level stagger with
        // `viewport once` leaves a card that's appended after the group already
        // animated stuck at `hidden` (invisible but occupying space). Self-
        // triggered whileInView always converges to visible on mount.
        <div className="space-y-3">
          {certificates.map((certificate) => (
            <Motion
              key={certificate.id}
              variants={fadeInUp}
              initial="hidden"
              whileInView="visible"
              viewport={{ once: true, margin: "-40px" }}
            >
              <StudentCertificateCard certificate={certificate} onViewDetail={onViewDetail} />
            </Motion>
          ))}
        </div>
      ) : (
        <CertificateGroupEmpty config={config} />
      )}
    </div>
  );
}