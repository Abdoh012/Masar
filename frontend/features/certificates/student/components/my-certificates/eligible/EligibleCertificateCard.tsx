import { Briefcase, CalendarCheck2 } from "lucide-react";

import type { EligibleTraining } from "../../../types";
import { formatShortDate } from "../constants";
import { EligibleRequestAction } from "./EligibleRequestAction";

interface EligibleCertificateCardProps {
  training: EligibleTraining;
}

// EligibleCertificateCard: one completed-training row inside the section's
// grouped eligible panel. Server leaf — receives one training, renders its
// title, company, and completion date. The row's single border/shadow shell
// lives on the panel (EligibleSectionContainer divides rows with hairlines);
// this row only pads the content and adds a primary-tint hover wash so it
// stays interactive-feeling despite the compact layout. The Request trigger
// is a client leaf (EligibleRequestAction) so interactivity stays at the
// boundary.
export function EligibleCertificateCard({
  training,
}: EligibleCertificateCardProps) {
  return (
    <div className="flex flex-col gap-3 p-5 transition-colors hover:bg-primary-tint sm:flex-row sm:items-center sm:justify-between">
      <div className="flex items-start gap-3 sm:items-center">
        <span className="flex size-10 shrink-0 items-center justify-center rounded-full bg-secondary-tint text-secondary-text">
          <Briefcase className="size-4" />
        </span>
        <div className="min-w-0">
          <p className="truncate font-sans text-base font-semibold text-foreground">
            {training.listingTitle}
          </p>
          <p className="truncate text-sm text-muted-foreground">
            {training.companyName}
          </p>
          {training.completedOn ? (
            <p className="mt-1 flex items-center gap-1.5 text-xs text-muted-foreground">
              <CalendarCheck2 className="size-3.5" />
              Completed {formatShortDate(training.completedOn)}
            </p>
          ) : null}
        </div>
      </div>

      <EligibleRequestAction training={training} />
    </div>
  );
}