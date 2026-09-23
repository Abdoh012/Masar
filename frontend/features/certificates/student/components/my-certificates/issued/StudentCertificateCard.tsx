import { AlertTriangle, Award, CalendarCheck2, ShieldCheck } from "lucide-react";

import { cn } from "@/shared/lib/utils";

import type { StudentCertificate } from "../../../types";
import { STATUS_ICONS } from "../CertificateStatusBadge";
import {
  CERTIFICATE_DATE_META,
  STATUS_DISPLAY,
  formatShortDate,
} from "../constants";
import { CertificateCardActions } from "./CertificateCardActions";

const CertificateGradeIcon = Award;

interface StudentCertificateCardProps {
  certificate: StudentCertificate;
}

// StudentCertificateCard: one certificate-record row inside a group's grouped
// panel (same pattern as the eligible section — the panel owns the
// border/shadow, this row only pads the content and adds a primary-tint hover
// wash). Renders the record's real data from the state-specific endpoint: a
// status tile (warning clock for requested, success check for issued, muted ban
// for revoked), the training title, company + field, the certificate number
// when one exists, the state's date line (Requested/Issued/Revoked via
// CERTIFICATE_DATE_META), and the revocation reason on revoked records.
// Interactivity lives in the client CertificateCardActions leaf.
export function StudentCertificateCard({
  certificate,
}: StudentCertificateCardProps) {
  const meta = STATUS_DISPLAY[certificate.status];
  const Icon = STATUS_ICONS[meta.icon];
  const dateMeta = CERTIFICATE_DATE_META[certificate.status];
  const dateValue = dateMeta.field ? certificate[dateMeta.field] : undefined;

  return (
    <div className="flex flex-col gap-4 p-5 transition-colors hover:bg-primary-tint sm:flex-row sm:items-center sm:justify-between">
      <div className="flex items-start gap-3 sm:items-center">
        <span
          className={cn(
            "flex size-10 shrink-0 items-center justify-center rounded-full",
            meta.badge,
          )}
        >
          <Icon className="size-5" />
        </span>

        <div className="min-w-0">
          <p className="truncate font-sans text-base font-semibold text-foreground">
            {certificate.listingTitle}
          </p>
          <p className="truncate text-sm text-muted-foreground">
            {certificate.companyName}
            {certificate.field ? ` — ${certificate.field}` : ""}
          </p>

          {certificate.certNumber ? (
            <p className="mt-1.5 flex items-center gap-1.5 font-mono text-xs text-secondary-text">
              <ShieldCheck className="size-3.5" />
              {certificate.certNumber}
            </p>
          ) : null}

          {dateValue ? (
            <p className="mt-2 flex items-center gap-1.5 text-xs text-muted-foreground">
              <CalendarCheck2 className="size-3.5" />
              {dateMeta.label} {formatShortDate(dateValue)}
            </p>
          ) : null}

          {certificate.gradeLabel ? (
            <p className="mt-1.5 flex items-center gap-1.5 text-xs font-medium text-primary-text">
              <CertificateGradeIcon className="size-3.5" />
              {certificate.gradeLabel}
              {certificate.grade ? ` (${certificate.grade})` : null}
            </p>
          ) : null}

          {certificate.revokeReason ? (
            <p className="mt-1.5 flex items-center gap-1.5 text-xs text-error-fg">
              <AlertTriangle className="size-3.5" />
              {certificate.revokeReason}
            </p>
          ) : null}
        </div>
      </div>

      <CertificateCardActions certificate={certificate} />
    </div>
  );
}