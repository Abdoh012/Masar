import { DashboardSection } from "@/shared/components/dashboard-section/DashboardSection";
import { DashboardSectionHeading } from "@/shared/components/dashboard-section/DashboardSectionHeading";

import { NOTIFICATIONS_LABELS } from "./constants";
import NoNotifications from "./NoNotifications";
import Notifications from "./Notifications";
import type { AppNotification } from "../../types";

interface RecentNotificationsProps {
  notifications: AppNotification[];
}

// RecentNotifications: the student's recent notifications, or "Nothing new".
//
// The feed arrives from the dashboard orchestrator, which reads it off
// GET /students/dashboard — the previous build rendered a static list of three
// invented items, identical for every student.
//
// The section deliberately has no "view all" link: there is no notifications
// centre route in the app yet, so the link this card used to carry resolved to a
// 404. Nothing is hidden instead — the endpoint returns at most five rows and
// the card renders all of them, so the whole feed is visible right here.
export function RecentNotifications({
  notifications,
}: RecentNotificationsProps) {
  const isEmpty = notifications.length === 0;

  return (
    <DashboardSection>
      <DashboardSectionHeading title={NOTIFICATIONS_LABELS.title} />

      {isEmpty ? <NoNotifications /> : <Notifications notifications={notifications} />}
    </DashboardSection>
  );
}
