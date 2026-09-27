import { NotificationRow } from "./NotificationRow";
import type { AppNotification } from "../../types";

interface NotificationsProps {
  notifications: AppNotification[];
}

// Leaf: the notification list, one row per entry — the whole feed, with no
// inner scroll area.
//
// The previous build capped the list at `max-h-72` and scrolled whatever did
// not fit, which left the card looking truncated: a scrollbar inside a card that
// already sits next to a full-height certificate document reads as "there is
// more, somewhere else". It also bought nothing, because the endpoint already
// returns at most five rows (`NotificationService::list(..., ['limit' => 5])`).
//
// So the list is simply laid out at its natural height. `flex-1` lets it claim
// whatever height the card ends up with — the card shares a row with the
// certificates section, which stretches to the taller of the two — and the rows
// space evenly from the top rather than leaving a void underneath.
export default function Notifications({ notifications }: NotificationsProps) {
  return (
    <ul className="mt-3 flex flex-1 flex-col">
      {notifications.map((notification) => (
        <NotificationRow key={notification.id} notification={notification} />
      ))}
    </ul>
  );
}
