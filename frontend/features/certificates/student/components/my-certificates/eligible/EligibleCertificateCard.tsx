import { Briefcase, CalendarCheck2 } from "lucide-react";

import { Button } from "@/shared/components/ui/button";

import type { EligibleTraining } from "../../../types";
import { formatShortDate } from "../constants";

interface EligibleCertificateCardProps {
  training: EligibleTraining;
  onRequest: (training: EligibleTraining) => void;
}

// EligibleCertificateCard: one completed-training row the student can request a
// certificate for, with the Request action. Leaf — receives one training + the
// request callback, renders nothing else.
export function EligibleCertificateCard({ training, onRequest }: EligibleCertificateCardProps) {
  return (
    <div className="flex flex-col gap-3 rounded-xl border border-border bg-card p-5 sm:flex-row sm:items-center sm:justify-between">
      <div className="flex items-start gap-3 sm:items-center">
        <span className="flex size-10 shrink-0 items-center justify-center rounded-full bg-secondary-tint text-secondary-text">
          <Briefcase className="size-4" />
        </span>
        <div className="min-w-0">
          <p className="truncate font-sans text-base font-semibold text-foreground">
            {training.listingTitle}
          </p>
          <p className="truncate text-sm text-muted-foreground">{training.companyName}</p>
          <p className="mt-1 flex items-center gap-1.5 text-xs text-muted-foreground">
            <CalendarCheck2 className="size-3.5" />
            Completed {formatShortDate(training.completedOn)}
          </p>
          {training.mayLeadToHire ? (
            <p className="mt-1.5 inline-flex rounded-full bg-primary-tint px-2.5 py-0.5 text-xs font-medium text-primary-text">
              May lead to hire
            </p>
          ) : null}
        </div>
      </div>

      <Button
        type="button"
        size="sm"
        className="shrink-0 self-start sm:self-center cursor-pointer"
        onClick={() => onRequest(training)}
      >
        Request certificate
      </Button>
    </div>
  );
}
