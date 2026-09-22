import { Check, Clock } from "lucide-react";

import Motion from "@/shared/components/animation/Motion";
import { fadeInUp } from "@/shared/lib/animations";
import { cn } from "@/shared/lib/utils";

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
}

// CertificateGroup: one bucket inside the "Your certificates" section — the
// requested (still awaiting confirmation) bucket or the issued/terminal
// bucket. Renders its tinted heading and, per item, a StudentCertificateCard;
// falls back to its own empty state when the bucket is empty. Placeholder
// render until the records list is wired — one sample card, no data props.
export function CertificateGroup({ config }: CertificateGroupProps) {
  const Icon = GROUP_ICONS[config.icon];

  return (
    <div className="space-y-3">
      <div className="flex items-center gap-2">
        <span className={cn("flex size-6 items-center justify-center rounded-full", config.accent)}>
          <Icon className="size-3.5" />
        </span>
        <h3 className="text-sm font-semibold text-primary-text">{config.title}</h3>
      </div>

      {true ? (
        <Motion
          variants={fadeInUp}
          initial="hidden"
          whileInView="visible"
          viewport={{ once: true, margin: "-40px" }}
        >
          <StudentCertificateCard />
        </Motion>
      ) : (
        <CertificateGroupEmpty config={config} />
      )}
    </div>
  );
}