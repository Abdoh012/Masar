import { Award, Briefcase, Info, type LucideIcon } from "lucide-react";

import type { AppNotification, NotificationType } from "../../types";

// How each notification type is presented: its icon, the tint of that icon's
// disc, and the accent colour of its title. Application updates carry brand
// navy (primary), certificates the seal gold (secondary), and system notices
// stay neutral so a routine message never competes with a real update.
export const NOTIFICATION_TYPE_STYLES: Record<
  NotificationType,
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

// The type an unrecognised notification falls back to. The data layer already
// degrades unknown kinds to "system", so this is the second line of defence for
// a caller that assembles rows by hand.
export const DEFAULT_NOTIFICATION_TYPE: NotificationType = "system";

export const NOTIFICATIONS_LABELS = {
  title: "Recent notifications",
} as const;

/** Relative time for a notification row, from the API's ISO-8601 timestamp.
 *
 *  Hand-written rather than pulled from a date library: this is the only place
 *  the dashboard needs "how long ago", and it is a handful of comparisons. The
 *  timestamp keeps its UTC offset and is parsed with `new Date()`, so the
 *  result is correct in any host timezone. Boundaries step through
 *  seconds → minutes → hours → days, and anything older than a week falls back
 *  to an absolute date — beyond that "31 days ago" stops being useful, and a
 *  month-old notification is better read as a date. */
export function formatNotificationTime(createdAt: string): string {
  if (!createdAt) return "";

  const timestamp = new Date(createdAt).getTime();
  if (Number.isNaN(timestamp)) return "";

  const elapsedSeconds = Math.floor((Date.now() - timestamp) / 1000);

  // A timestamp in the future (clock skew between app and API) reads as "now"
  // rather than as a negative age.
  if (elapsedSeconds < 60) return "just now";

  const elapsedMinutes = Math.floor(elapsedSeconds / 60);
  if (elapsedMinutes < 60) {
    return `${elapsedMinutes}m ago`;
  }

  const elapsedHours = Math.floor(elapsedMinutes / 60);
  if (elapsedHours < 24) {
    return `${elapsedHours}h ago`;
  }

  const elapsedDays = Math.floor(elapsedHours / 24);
  if (elapsedDays < 7) {
    return elapsedDays === 1 ? "yesterday" : `${elapsedDays}d ago`;
  }

  return new Intl.DateTimeFormat("en-US", {
    day: "numeric",
    month: "short",
    timeZone: "UTC",
  }).format(new Date(timestamp));
}
