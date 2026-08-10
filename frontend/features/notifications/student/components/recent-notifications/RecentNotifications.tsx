import Link from "next/link";

import Motion from "@/shared/components/animation/components/Motion";
import { fadeInUp } from "@/shared/lib/animations";
import { RECENT_NOTIFICATIONS } from "./constants";
import NoNotifications from "./NoNotifications";
import Notifications from "./Notifications";

// RecentNotifications: first 3 notifications + link to the full center, or "Nothing new".
export function RecentNotifications() {
  const isEmpty = RECENT_NOTIFICATIONS.length === 0;

  return (
    <Motion
      variants={fadeInUp}
      initial="hidden"
      whileInView="visible"
      viewport={{ once: true, margin: "-40px" }}
      transition={{ duration: 0.24, ease: "easeOut" }}
      className="flex h-full flex-col rounded-2xl border border-border bg-card p-5 shadow-card"
    >
      <div className="flex items-center justify-between gap-2">
        {/* Header */}
        <h2 className="text-base font-semibold text-primary-text">
          Recent notifications
        </h2>

        {/* View all */}
        {!isEmpty ? (
          <Link
            href="/notifications"
            className="inline-flex shrink-0 items-center gap-1.5 text-sm font-medium text-primary transition-colors hover:text-primary/80 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
          >
            View all notifications
          </Link>
        ) : null}
      </div>

      {isEmpty ? <NoNotifications /> : <Notifications />}
    </Motion>
  );
}
