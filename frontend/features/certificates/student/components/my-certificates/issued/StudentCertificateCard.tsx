import {
  BadgeCheck,
  CalendarCheck2,
  Download,
  ShieldCheck,
} from "lucide-react";

import { Button } from "@/shared/components/ui/button";

import { formatShortDate } from "../constants";

// StudentCertificateCard: one certificate-record row inside the issued/terminal
// groups. Placeholder render until the records list (GET /certificates) is
// wired into the section — no props, hardcoded sample values, inert actions.
export function StudentCertificateCard() {
  return (
    <div className="flex flex-col gap-4 rounded-xl border border-border bg-card p-5 sm:flex-row sm:items-center sm:justify-between">
      <div className="flex items-start gap-3 sm:items-center">
        <span className="flex size-10 shrink-0 items-center justify-center rounded-full bg-success-bg text-success-fg">
          <BadgeCheck className="size-5" />
        </span>

        <div className="min-w-0">
          <p className="truncate font-sans text-base font-semibold text-foreground">
            Certificate title
          </p>
          <p className="truncate text-sm text-muted-foreground">
            Company — Field
          </p>
          <p className="mt-1.5 flex items-center gap-1.5 font-mono text-xs text-secondary-text">
            <ShieldCheck className="size-3.5" />
            CRT-2025-0001
          </p>
          <p className="mt-2 flex items-center gap-1.5 text-xs text-muted-foreground">
            <CalendarCheck2 className="size-3.5" />
            Issued {formatShortDate("2025-01-01")}
          </p>
        </div>
      </div>

      <div className="flex shrink-0 items-center gap-2 self-start sm:self-center">
        <Button
          type="button"
          size="sm"
          variant="outline"
          className="cursor-pointer"
        >
          View certificate
        </Button>
        <Button
          type="button"
          size="sm"
          variant="default"
          className="cursor-pointer"
        >
          <Download className="size-4" />
          <span className="sr-only sm:not-sr-only">Download</span>
        </Button>
      </div>
    </div>
  );
}