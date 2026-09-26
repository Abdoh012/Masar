import Link from "next/link";

import { DashboardSection } from "@/shared/components/dashboard-section/DashboardSection";
import { DashboardSectionHeading } from "@/shared/components/dashboard-section/DashboardSectionHeading";

import { RECENT_NOTIFICATIONS } from "./constants";
import NoNotifications from "./NoNotifications";
import Notifications from "./Notifications";

// RecentNotifications: first 3 notifications + link to the full center, or "Nothing new".
export function RecentNotifications() {
  const isEmpty = RECENT_NOTIFICATIONS.length === 0;

  return (
    <DashboardSection>
      <DashboardSectionHeading
        title="Recent notifications"
        action={
          !isEmpty ? (
            <Link
              href="/notifications"
              className="inline-flex shrink-0 items-center gap-1.5 text-sm font-medium text-primary transition-colors hover:text-primary/80 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
            >
              View all notifications
            </Link>
          ) : null
        }
      />

      {isEmpty ? <NoNotifications /> : <Notifications />}
    </DashboardSection>
  );
}
