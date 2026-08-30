import { AlertTriangle, Ban, Check, Clock } from "lucide-react";

import { cn } from "@/shared/lib/utils";

import type { CertificateStatus } from "../../types";
import { STATUS_DISPLAY } from "./constants";

const STATUS_ICONS = {
  clock: Clock,
  check: Check,
  alert: AlertTriangle,
  ban: Ban,
} as const;

interface CertificateStatusBadgeProps {
  status: CertificateStatus;
  className?: string;
}

// CertificateStatusBadge: the status pill for a certificate record. Maps a
// CertificateStatus to its label, tinted badge classes, and leading icon from
// the section-level STATUS_DISPLAY map. "Pending" reads as a warning clock,
// "issued"/"active" as a success check, and terminal states as a muted ban.
export function CertificateStatusBadge({ status, className }: CertificateStatusBadgeProps) {
  const meta = STATUS_DISPLAY[status];
  const Icon = STATUS_ICONS[meta.icon];

  return (
    <span
      className={cn(
        "inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium",
        meta.badge,
        className,
      )}
    >
      <Icon className={cn("size-3.5")} />
      {meta.label}
    </span>
  );
}
