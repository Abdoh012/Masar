// Role-level types for the notifications student (structure rules §14).

// The kinds the dashboard styles. Narrowed defensively by the data layer, so an
// unknown kind arriving from the notifications table degrades to "system"
// instead of breaking the icon lookup.
export type NotificationType = "application" | "certificate" | "system";

// One notification, as the API reports it. Field names mirror the backend
// presenter (`student_service_dashboard_notification`) rather than a display
// shape: `createdAt` is ISO-8601 with an explicit UTC offset and is formatted at
// render time, and `read` is the API's boolean rather than a pre-inverted
// "unread" flag.
export interface AppNotification {
  id: number;
  type: NotificationType;
  title: string;
  body: string;
  createdAt: string;
  read: boolean;
}
