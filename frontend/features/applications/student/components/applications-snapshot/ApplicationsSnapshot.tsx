import Link from "next/link";
import { FileText } from "lucide-react";

import { DashboardSection } from "@/shared/components/dashboard-section/DashboardSection";
import { DashboardSectionHeading } from "@/shared/components/dashboard-section/DashboardSectionHeading";

import {
  APPLICATION_STATUSES,
  RECENT_APPLICATIONS,
  STATUS_COUNT_KEYS,
  STATUS_COUNTS,
} from "./constants";
import type { StatusCounts } from "../../types";
import { RecentApplicationRow } from "./RecentApplicationRow";
import { StatusCountBadge } from "./StatusCountBadge";

const COUNTS_ZERO: StatusCounts = {
  applied: 0,
  accepted: 0,
  rejected: 0,
  withdrawn: 0,
};

// ApplicationsSnapshot: status-count tiles + up to 3 recent rows, or the empty state.
export function ApplicationsSnapshot() {
  const isEmpty =
    STATUS_COUNTS.applied === COUNTS_ZERO.applied &&
    STATUS_COUNTS.accepted === COUNTS_ZERO.accepted &&
    STATUS_COUNTS.rejected === COUNTS_ZERO.rejected &&
    STATUS_COUNTS.withdrawn === COUNTS_ZERO.withdrawn &&
    RECENT_APPLICATIONS.length === 0;

  return (
    <DashboardSection>
      <DashboardSectionHeading
        title="Applications snapshot"
        action={
          !isEmpty ? (
            <Link
              href="/applications"
              className="inline-flex shrink-0 items-center gap-1.5 text-sm font-medium text-primary-text transition-colors hover:text-primary/80 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
            >
              View all applications
            </Link>
          ) : null
        }
      />

      {/* If there are no applications, show the empty state */}
      {isEmpty ? (
        <div className="mt-5 flex flex-1 flex-col items-center justify-center gap-2 rounded-xl border border-dashed border-border bg-background px-4 py-8 text-center">
          <FileText
            aria-hidden="true"
            className="size-6 text-muted-foreground"
          />
          <p className="text-sm font-semibold text-foreground">
            No applications yet
          </p>
          <p className="text-xs text-muted-foreground">
            Applications you submit will appear here.
          </p>
        </div>
      ) : (
        <>
          {/* Status count tiles — always 4 columns (2 on mobile) */}
          <div className="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
            {APPLICATION_STATUSES.map((status) => (
              <StatusCountBadge
                key={status}
                label={status}
                count={STATUS_COUNTS[STATUS_COUNT_KEYS[status]]}
                status={status}
              />
            ))}
          </div>

          {/* Recent applications list (up to 3 rows) */}
          <ul className="mt-3">
            {RECENT_APPLICATIONS.slice(0, 3).map((row) => (
              <RecentApplicationRow key={row.id} row={row} />
            ))}
          </ul>
        </>
      )}
    </DashboardSection>
  );
}
