// Role-level types for the notifications student (structure rules §14).

export type NotificationType = "application" | "certificate" | "system";

export interface AppNotification {
  id: string;
  type: NotificationType;
  title: string;
  body: string;
  timestamp: string;
  unread: boolean;
}