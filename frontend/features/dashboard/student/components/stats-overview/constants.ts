import { Award, Briefcase, FileText, type LucideIcon } from "lucide-react";

import type { DashboardQuickStats } from "@/features/dashboard/student/types";

/** The three counters the strip shows, in display order. `key` indexes
 *  `quick_stats`; `label` is the card's own copy. These are the three
 *  headline numbers from GET /students/dashboard — `profile_completion` is
 *  deliberately not a card, it belongs to the profile header.
 *
 *  `accentClass` gives each card its own tint for the figure and the corner
 *  wash. They are all brand tokens rather than status colours, because these
 *  are totals and carry no state — a green "1" here would read as a success
 *  signal, which is exactly what the reserved `sage` token exists to prevent
 *  and what these counters must not borrow. */
export interface DashboardStat {
  key: keyof DashboardQuickStats;
  label: string;
  description: string;
  icon: LucideIcon;
  accentClass: string;
}

export const DASHBOARD_STATS: DashboardStat[] = [
  {
    key: "activeTrainings",
    label: "Active trainings",
    description: "Trainings you've been accepted into",
    icon: Briefcase,
    accentClass: "text-primary-text",
  },
  {
    key: "applications",
    label: "Applications",
    description: "Listings you've applied to",
    icon: FileText,
    accentClass: "text-primary-text/80",
  },
  {
    key: "issuedCertificates",
    label: "Issued certificates",
    description: "Trainings you've completed and certified",
    icon: Award,
    accentClass: "text-secondary-text",
  },
];
