import { RECENT_NOTIFICATIONS } from "./constants";
import { NotificationRow } from "./NotificationRow";

export default function Notifications() {
  return (
    <ul className="mt-2 flex-1">
      {RECENT_NOTIFICATIONS.slice(0, 3).map((notification) => (
        <NotificationRow key={notification.id} notification={notification} />
      ))}
    </ul>
  );
}
