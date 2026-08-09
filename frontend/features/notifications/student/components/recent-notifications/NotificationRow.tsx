import { Award, Briefcase, Info, type LucideIcon } from "lucide-react";

import type { AppNotification } from "./constants";

const TYPE_ICONS: Record<AppNotification["type"], LucideIcon> = {
  application: Briefcase,
  certificate: Award,
  system: Info,
};

// Leaf: one notification row. Icon resolves from the type fan-in here (UI concern,
// not mock data), unread dot is text-accessible via aria-label.
export function NotificationRow({ notification }: { notification: AppNotification }) {
  const Icon = TYPE_ICONS[notification.type];

  return (
    <li className="flex items-start gap-3 border-t border-border py-3 first:border-t-0">
      <span
        aria-hidden="true"
        className="flex size-8 shrink-0 items-center justify-center rounded-full bg-primary-tint text-primary-text"
      >
        <Icon className="size-4" />
      </span>

      <div className="min-w-0 flex-1">
        <div className="flex items-center gap-2">
          <p className="truncate text-sm font-medium text-foreground">{notification.title}</p>
          {notification.unread ? (
            <span
              aria-label="Unread"
              title="Unread"
              className="size-2 shrink-0 rounded-full bg-primary"
            />
          ) : null}
        </div>
        <p className="line-clamp-2 text-xs text-muted-foreground">{notification.body}</p>
      </div>

      <time className="shrink-0 pt-0.5 font-mono text-xs text-muted-foreground">
        {notification.timestamp}
      </time>
    </li>
  );
}