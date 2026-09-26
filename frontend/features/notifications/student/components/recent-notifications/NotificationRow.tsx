import { NOTIFICATION_TYPE_STYLES } from "./constants";
import type { AppNotification } from "../../types";

// Leaf: one notification row. Icon + accent resolve from the type fan-in in
// constants, unread dot is text-accessible via aria-label.
export function NotificationRow({ notification }: { notification: AppNotification }) {
  const { icon: Icon, disc, title } = NOTIFICATION_TYPE_STYLES[notification.type];

  return (
    <li className="group flex items-start gap-3 border-t border-border py-3 transition-colors duration-200 first:border-t-0 hover:bg-background/60">
      <span
        aria-hidden="true"
        className={`flex size-9 shrink-0 items-center justify-center rounded-full ${disc}`}
      >
        <Icon className="size-4" />
      </span>

      <div className="min-w-0 flex-1">
        <div className="flex items-center gap-2">
          <p className={`truncate text-sm font-semibold ${title}`}>
            {notification.title}
          </p>
          {notification.unread ? (
            <span
              aria-label="Unread"
              title="Unread"
              className="size-2 shrink-0 rounded-full bg-primary"
            />
          ) : null}
        </div>
        <p className="line-clamp-2 text-sm text-muted-foreground">{notification.body}</p>
      </div>

      <time className="shrink-0 pt-0.5 font-mono text-xs text-muted-foreground">
        {notification.timestamp}
      </time>
    </li>
  );
}
