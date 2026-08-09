import type { AppNotification } from "../../types";

// Mock notifications data (UI-only).

export const RECENT_NOTIFICATIONS: AppNotification[] = [
  {
    id: "n-1",
    type: "application",
    title: "Application accepted",
    body: "Hala Bank accepted your application for Software Engineering Trainee.",
    timestamp: "2h ago",
    unread: true,
  },
  {
    id: "n-2",
    type: "certificate",
    title: "Certificate confirmed",
    body: "Your Software Engineering certificate is verified.",
    timestamp: "yesterday",
    unread: true,
  },
  {
    id: "n-3",
    type: "system",
    title: "New trainings match your field",
    body: "New trainings were added to Recommended trainings.",
    timestamp: "3d ago",
    unread: false,
  },
];

// Empty variant: empty array → "Nothing new".
export const NOTIFICATIONS_EMPTY: AppNotification[] = [];