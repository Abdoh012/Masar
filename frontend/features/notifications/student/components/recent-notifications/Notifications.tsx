import { NOTIFICATIONS_SCROLL_AREA_CLASS, RECENT_NOTIFICATIONS } from "./constants";
import { NotificationRow } from "./NotificationRow";

// Leaf: the notification list. `flex-1 min-h-0` lets it absorb whatever height
// the card ends up with (so it fills a short row instead of leaving a gap), and
// the max-height caps it so a long feed scrolls inside the card rather than
// stretching the row. "View all notifications" carries the overflow.
export default function Notifications() {
  return (
    <ul className={`mt-3 flex-1 ${NOTIFICATIONS_SCROLL_AREA_CLASS}`}>
      {RECENT_NOTIFICATIONS.slice(0, 3).map((notification) => (
        <NotificationRow key={notification.id} notification={notification} />
      ))}
    </ul>
  );
}
