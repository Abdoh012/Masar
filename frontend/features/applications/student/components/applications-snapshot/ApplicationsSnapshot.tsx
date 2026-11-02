import Link from "next/link";
import { FileText } from "lucide-react";

import { DashboardSection } from "@/shared/components/dashboard-section/DashboardSection";
import { DashboardSectionHeading } from "@/shared/components/dashboard-section/DashboardSectionHeading";

import type { RecentApplicationRow, StatusCounts } from "../../types";
import {
  APPLICATION_STATUSES,
  SNAPSHOT_EMPTY,
  STATUS_COUNT_KEYS,
} from "./constants";
import { RecentApplicationList } from "./RecentApplicationList";
import { SnapshotTotals } from "./SnapshotTotals";
import { StatusCountBadge } from "./StatusCountBadge";

interface ApplicationsSnapshotProps {
  counts: StatusCounts;
  recent: RecentApplicationRow[];
  /** Every application the student has ever submitted (`total`) and the share
   *  that were accepted (`acceptance_rate`) — the two figures the four status
   *  tiles break down but cannot state themselves. */
  total: number;
  acceptanceRate: number;
}

// ApplicationsSnapshot: the headline totals, status-count tiles, and up to 3
// recent rows, or the empty state. Every figure arrives from the dashboard
// orchestrator, which reads them off GET /students/dashboard.
//
// The section is full-width, which is what gives the four tiles room: the 4-up
// grid only engages at `lg`, where each tile has real width, and stacks to two
// columns below that instead of squeezing four into a phone or a half-width
// cell. The tiles themselves are compact and horizontal, so two columns on a
// phone still read as a full tile rather than a truncated one.
export function ApplicationsSnapshot({
  counts,
  recent,
  total,
  acceptanceRate,
}: ApplicationsSnapshotProps) {
  const isEmpty = counts.total === 0 && recent.length === 0;

  return (
    <DashboardSection>
      <DashboardSectionHeading
        title="Applications snapshot"
        action={
          !isEmpty ? (
            <div className="flex flex-wrap items-center justify-end gap-x-4 gap-y-2">
              <SnapshotTotals
                total={total}
                acceptanceRate={acceptanceRate}
              />
              <Link
                href="/applications"
                className="inline-flex shrink-0 items-center gap-1.5 text-sm font-medium text-primary-text transition-colors hover:text-primary/80 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
              >
                View all applications
              </Link>
            </div>
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
            {SNAPSHOT_EMPTY.title}
          </p>
          <p className="text-xs text-muted-foreground">
            {SNAPSHOT_EMPTY.message}
          </p>
        </div>
      ) : (
        <>
          {/* Status count tiles — two columns until there is room for all four */}
          <div className="mt-5 grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
            {APPLICATION_STATUSES.map((status) => (
              <StatusCountBadge
                key={status}
                label={status}
                count={counts[STATUS_COUNT_KEYS[status]]}
                status={status}
              />
            ))}
          </div>

          {/* Recent applications list */}
          <RecentApplicationList rows={recent} />
        </>
      )}
    </DashboardSection>
  );
}
