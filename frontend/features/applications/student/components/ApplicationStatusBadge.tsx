import { cn } from "@/shared/lib/utils";

import type { ApplicationStatus } from "../types";
import { STATUS_BADGE_CLASSES } from "./applications-snapshot/constants";

interface ApplicationStatusBadgeProps {
  status: ApplicationStatus;
  className?: string;
}

// ApplicationStatusBadge: the status pill for an application, shared by the
// dashboard's recent rows and the My Applications cards — the two surfaces that
// previously hand-rolled it and drifted apart. Shape, colour and label live
// here; the colour comes from STATUS_BADGE_CLASSES, the same map the status
// stat tiles read, so a status can't be one colour in one place and another
// colour somewhere else. Accepted is `success`, never `sage` (reserved for the
// confirmed-hire signal). Default geometry is the project's small-pill idiom
// (PaymentStatusBadge, "May lead to hire", CertificateStatusBadge); pass
// `className` only for a caller's own layout treatment, e.g. the recent row's
// fixed min-width that keeps its right edge still.
export function ApplicationStatusBadge({ status, className }: ApplicationStatusBadgeProps) {
  return (
    <span className={cn("rounded-full px-2.5 py-0.5 text-xs font-medium", STATUS_BADGE_CLASSES[status], className)}>
      {status}
    </span>
  );
}
