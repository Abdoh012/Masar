import { Award, Briefcase, Info, type LucideIcon } from "lucide-react";

import type { AppNotification } from "../../types";

// How each notification type is presented: its icon, the tint of that icon's
// disc, and the accent colour of its title. Application updates carry brand
// navy (primary), certificates the seal gold (secondary), and system notices
// stay neutral so a routine message never competes with a real update.
export const NOTIFICATION_TYPE_STYLES: Record<
  AppNotification["type"],
  { icon: LucideIcon; disc: string; title: string }
> = {
  application: {
    icon: Briefcase,
    disc: "bg-primary-tint text-primary-text",
    title: "text-primary-text",
  },
  certificate: {
    icon: Award,
    disc: "bg-secondary-tint text-secondary-text",
    title: "text-secondary-text",
  },
  system: {
    icon: Info,
    disc: "bg-neutral-badge-bg text-neutral-badge-fg",
    title: "text-foreground",
  },
};

// The scroll window for the dashboard's notification feed. Capping it is what
// keeps the Certificates + Notifications row a predictable height: without a
// ceiling the taller card grows without bound and the shorter one is left with
// dead space underneath. Sized for roughly four rows; `min-h-0` is what allows
// a flex child to actually scroll instead of refusing to shrink.
export const NOTIFICATIONS_SCROLL_AREA_CLASS =
  "min-h-0 max-h-72 overflow-y-auto overscroll-contain pr-1";

// Mock notifications data (UI-only).

export const RECENT_NOTIFICATIONS: AppNotification[] = [
  {
    id: "n-1",
    type: "application",
    title: "Application accepted",
    body: "Hala Bank accepted your application for Software Engineering Trainee.",
    timestamp: "2h ago",
    unread: true,
  },
  {
    id: "n-2",
    type: "certificate",
    title: "Certificate confirmed",
    body: "Your Software Engineering certificate is verified.",
    timestamp: "yesterday",
    unread: true,
  },
  {
    id: "n-3",
    type: "system",
    title: "New trainings match your field",
    body: "New trainings were added to Recommended trainings.",
    timestamp: "3d ago",
    unread: false,
  },
];

// Empty variant: empty array → "Nothing new".
export const NOTIFICATIONS_EMPTY: AppNotification[] = [];