import {
  formatNotificationTime,
  NOTIFICATION_TYPE_STYLES,
  DEFAULT_NOTIFICATION_TYPE,
} from "./constants";
import type { AppNotification } from "../../types";

// Leaf: one notification row. Icon + accent resolve from the type fan-in in
// constants (falling back to the neutral treatment for a kind this UI doesn't
// style), the timestamp is formatted from the API's ISO-8601 here, and the
// unread dot is text-accessible via aria-label.
export function NotificationRow({
  notification,
}: {
  notification: AppNotification;
}) {
  const {
    icon: Icon,
    disc,
    title,
  } = NOTIFICATION_TYPE_STYLES[notification.type] ??
  NOTIFICATION_TYPE_STYLES[DEFAULT_NOTIFICATION_TYPE];

  const time = formatNotificationTime(notification.createdAt);

  return (
    // `flex-1` lets the row absorb whatever height the card has to give, so the
    // feed fills the card edge to edge instead of leaving a gap under the last
    // notification. `items-center` keeps the icon and text centred in whatever
    // space the row ends up with; on a short feed, where the rows sit at their
    // natural height, it is indistinguishable from top alignment.
    <li className="group flex flex-1 items-center gap-3 border-t border-border py-3 transition-colors duration-200 first:border-t-0">
      <span
        className={`flex size-9 shrink-0 items-center justify-center rounded-full ${disc}`}
      >
        <Icon className="size-4" />
      </span>

      <div className="min-w-0 flex-1">
        <div className="flex items-center gap-2">
          <p className={`truncate text-sm font-semibold ${title}`}>
            {notification.title}
          </p>
          {!notification.read ? (
            <span
              title="Unread"
              className="size-2 shrink-0 rounded-full bg-primary"
            />
          ) : null}
        </div>
        <p className="line-clamp-2 text-sm text-muted-foreground">
          {notification.body}
        </p>
      </div>

      {time ? (
        <time
          dateTime={notification.createdAt}
          className="shrink-0 pt-0.5 font-mono text-xs text-muted-foreground"
        >
          {time}
        </time>
      ) : null}
    </li>
  );
}
