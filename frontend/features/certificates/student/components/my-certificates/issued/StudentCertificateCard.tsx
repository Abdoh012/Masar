import {
  BadgeCheck,
  CalendarCheck2,
  Download,
  ShieldCheck,
} from "lucide-react";

import { Button } from "@/shared/components/ui/button";
import { cn } from "@/shared/lib/utils";

import type { StudentCertificate } from "../../../types";
import { formatShortDate } from "../constants";
import { CertificateStatusBadge } from "../CertificateStatusBadge";

interface StudentCertificateCardProps {
  certificate: StudentCertificate;
  onViewDetail: (certificate: StudentCertificate) => void;
}

// StudentCertificateCard: one certificate-record row in the issued/terminal
// section. Shows the status badge, listing + company, the certificate number
// (mono, when issued), dates, and contextual actions: view certificate +
// download for live certificates; a terminal-status explainer otherwise. Leaf —
// renders a single certificate; the detail dialog lives in the section
// container.
export function StudentCertificateCard({
  certificate,
  onViewDetail,
}: StudentCertificateCardProps) {
  const isLive = certificate.canDownload && certificate.canVerify;

  return (
    <div className="flex flex-col gap-4 rounded-xl border border-border bg-card p-5 sm:flex-row sm:items-start sm:justify-between">
      <div className="flex items-start gap-3">
        <span
          className={cn(
            "flex size-10 shrink-0 items-center justify-center rounded-full",
            isLive
              ? "bg-success-bg text-success-fg"
              : "bg-primary-tint text-primary-text",
          )}
        >
          <BadgeCheck className="size-5" />
        </span>

        <div className="min-w-0">
          <div className="flex flex-wrap items-center gap-2">
            <CertificateStatusBadge status={certificate.status} />
            {certificate.mayLeadToHire ? (
              <span className="rounded-full bg-primary-tint px-2.5 py-0.5 text-xs font-medium text-primary-text">
                May lead to hire
              </span>
            ) : null}
          </div>

          <p className="mt-2 truncate font-sans text-base font-semibold text-foreground">
            {certificate.listingTitle}
          </p>
          <p className="truncate text-sm text-muted-foreground">
            {certificate.companyName} — {certificate.field}
          </p>

          {certificate.certNumber ? (
            <p className="mt-1.5 flex items-center gap-1.5 font-mono text-xs text-secondary-text">
              <ShieldCheck className="size-3.5" />
              {certificate.certNumber}
            </p>
          ) : null}

          <div className="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-muted-foreground">
            {certificate.issuedOn ? (
              <span className="flex items-center gap-1.5">
                <CalendarCheck2 className="size-3.5" />
                Issued {formatShortDate(certificate.issuedOn)}
              </span>
            ) : certificate.requestedOn ? (
              <span className="flex items-center gap-1.5">
                <CalendarCheck2 className="size-3.5" />
                Requested {formatShortDate(certificate.requestedOn)}
              </span>
            ) : null}
          </div>

          {!isLive && certificate.status === "revoked" ? (
            <p className="mt-2 text-xs text-muted-foreground">
              {certificate.revokeReason}
            </p>
          ) : null}
        </div>
      </div>

      <div className="flex shrink-0 items-center gap-2 self-start sm:self-center">
        <Button
          type="button"
          size="sm"
          variant="outline"
          onClick={() => onViewDetail(certificate)}
        >
          View certificate
        </Button>
        {isLive ? (
          <Button
            type="button"
            size="sm"
            variant="default"
            className="cursor-pointer"
            asChild
          >
            <a href="#" aria-disabled="true">
              <Download className="size-4" />
              <span className="sr-only sm:not-sr-only">Download</span>
            </a>
          </Button>
        ) : null}
      </div>
    </div>
  );
}
