import Link from "next/link";
import { BellOff } from "lucide-react";

import Motion from "@/shared/components/animation/Motion";
import { fadeInUp } from "@/shared/lib/animations";
import { RECENT_NOTIFICATIONS } from "./constants";
import { NotificationRow } from "./NotificationRow";

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
        <h2 className="text-base font-semibold text-primary-text">Recent notifications</h2>
        {!isEmpty ? (
          <Link
            href="/notifications"
            className="inline-flex shrink-0 items-center gap-1.5 text-sm font-medium text-primary transition-colors hover:text-primary/80 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
          >
            View all notifications
          </Link>
        ) : null}
      </div>

      {isEmpty ? (
        <div className="flex flex-1 flex-col items-center justify-center gap-2 rounded-xl border border-dashed border-border bg-background px-4 py-8 text-center">
          <BellOff aria-hidden="true" className="size-6 text-muted-foreground" />
          <p className="text-sm font-semibold text-foreground">Nothing new</p>
          <p className="text-xs text-muted-foreground">
            We&apos;ll let you know when something happens.
          </p>
        </div>
      ) : (
        <ul className="mt-2 flex-1">
          {RECENT_NOTIFICATIONS.slice(0, 3).map((notification) => (
            <NotificationRow key={notification.id} notification={notification} />
          ))}
        </ul>
      )}
    </Motion>
  );
}