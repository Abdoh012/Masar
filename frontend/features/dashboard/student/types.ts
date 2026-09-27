// Role-level types for the dashboard student role (structure rules §14).
// Every field mirrors GET /api/v1/students/dashboard after
// student/lib/normalize.ts has mapped the backend's snake_case presenter output
// (backend/app/modules/students/services/student_service.php → student_service_dashboard).

/** The authenticated student's own block. `profileCompletion` is computed by
 *  the backend (student_calculate_completion_percentage) and sent as an int
 *  0..100 under `student.profile_completion.percentage` — the frontend never
 *  derives it. */
export interface DashboardStudent {
  id: number;
  fullName: string;
  field: string | null;
  faculty: string | null;
  university: string | null;
  profileImageFileId: number | null;
  /** 0..100, straight from the backend. */
  profileCompletion: number;
}

/** The three headline counters rendered by the stats strip
 *  (`quick_stats`). `profile_completion` is also on this object in the API but
 *  is not a stat card — it belongs to the profile header. */
export interface DashboardQuickStats {
  activeTrainings: number;
  applications: number;
  issuedCertificates: number;
}

/** The student's latest accepted application and the training behind it, or
 *  null when they have never been accepted. The two date fields are ISO-8601
 *  with an explicit UTC offset (backend `application_iso8601`, which renders
 *  Africa/Cairo datetimes via `format('c')`) — so `+02:00` in winter and
 *  `+03:00` in summer, and `new Date()` resolves both to the right instant. */
export interface DashboardActiveTraining {
  id: number;
  title: string;
  company: string | null;
  type: string;
  /** Delivery format as stored on the training: in_person | remote | hybrid.
   *  Narrowed by lib/normalize.ts against the column's real enum. */
  mode: "in_person" | "remote" | "hybrid";
  isPaid: boolean;
  trialDays: number | null;
  startsAt: string | null;
  endsAt: string | null;
  /** The backend's calendar-day countdown (training_calculate_remaining_days):
   *  whole days from today to ends_at, floored at 0, null when ends_at is
   *  unset. Used for the human countdown only — never as the progress bar's
   *  percentage, which is derived from the dates above. */
  remainingDays: number | null;
}

/** The four status counts the snapshot tiles show, plus the totals the header
 *  reads. Mirrors `applications_snapshot`. */
export interface DashboardApplicationsSnapshot {
  total: number;
  applied: number;
  accepted: number;
  rejected: number;
  withdrawn: number;
  acceptanceRate: number;
}

/** The four status labels the snapshot tiles display. Declared here rather than
 *  imported from the applications feature (R6: features never import each
 *  other) — the two unions are structurally identical, so the value still
 *  assigns cleanly to the applications section's own `ApplicationStatus`. */
export type DashboardApplicationStatus =
  | "Applied"
  | "Accepted"
  | "Rejected"
  | "Withdrawn";

/** One entry of the snapshot's recent-applications list
 *  (`applications_snapshot.recent`). The backend reuses the All-tab card DTO
 *  here, so the same fields the My Applications page reads are present. */
export interface DashboardRecentApplication {
  id: number;
  trainingId: number;
  listingTitle: string;
  companyName: string;
  status: DashboardApplicationStatus;
  /** ISO-8601 of the row's most recent lifecycle action — the status's own
   *  timestamp (withdrawn_at / reviewed_at / applied_at), or applied_at when
   *  the status has none. Null when the API sent neither. */
  dateOn: string | null;
}

/* ---------------------------------------------------------------------------
 * Certificates section (`certificates`)
 * ------------------------------------------------------------------------- */

/** The certificate lifecycle counts the snapshot chip + count row read
 *  (`certificates.{eligible,pending,issued,revoked}`), all computed by the
 *  backend's certificate repository counters — `eligible` is the
 *  requestable set, `issued` the issued records, `revoked` the terminal one. */
export interface DashboardCertificateCounts {
  eligible: number;
  pending: number;
  issued: number;
  revoked: number;
}

/** One entry of `certificates.recent` — the backend's certificate record
 *  presenter (the same DTO /certificates/issued returns), narrowed to the
 *  fields the dashboard document actually prints. The nested `student` block is
 *  flattened to `studentName`/`studentField` because the certificate document
 *  reads the name off it, and no other section consumes the rest. */
export interface DashboardCertificateRecord {
  id: number;
  certificateNumber: string | null;
  status: string;
  companyName: string | null;
  trainingTitle: string;
  specializationName: string | null;
  grade?: string;
  gradeLabel?: string;
  /** MySQL datetime ("Y-m-d H:i:s") as the certificate repository stores it —
   *  rendered in Africa/Cairo, like every other date in the backend. */
  issuedAt: string | null;
  /** `student.full_name` — the name the certificate document prints. */
  studentName: string;
  /** `student.field`, falling back to the record's `specialization_name` when
   *  the student block carries no field. Named `field` to match the page's own
   *  certificate record, so the two shapes stay interchangeable. */
  field: string | null;
}

/** The certificates section: the four lifecycle counts plus a preview of the
 *  most recent records (the backend sends one). */
export interface DashboardCertificatesSection {
  counts: DashboardCertificateCounts;
  recent: DashboardCertificateRecord[];
}

/* ---------------------------------------------------------------------------
 * Recommended trainings section (`recommended_trainings`)
 * ------------------------------------------------------------------------- */

/** The training's participation mode — the backend's `type` on this card, which
 *  is the same enum as the listings browse grid's `mode`. */
export type DashboardTrainingMode = "observer" | "hands_on" | "project_based";

/** The training's delivery format — the backend's `mode` on this card, which is
 *  the same enum as the listings browse grid's `format`. */
export type DashboardTrainingFormat = "in_person" | "remote" | "hybrid";

/** One recommended training card. Field names follow the backend presenter
 *  (`student_service_dashboard_recommendation_card`) rather than the browse
 *  grid's, and the two enums above exist because the presenter swapped the
 *  browse grid's vocabulary: here `type` is the mode and `mode` is the format.
 *  `price`/`freeTrialDays` are null for a free training (the presenter gates
 *  both on `is_paid`, not on the column being NULL), and the three dates are
 *  ISO-8601 with an explicit offset. */
export interface DashboardRecommendedTraining {
  id: number;
  title: string;
  company: string | null;
  companyLogo: string | null;
  mode: DashboardTrainingMode;
  format: DashboardTrainingFormat;
  isPaid: boolean;
  price: number | null;
  freeTrialDays: number | null;
  createdAt: string | null;
  /** The last moment an application may be submitted — a different date from
   *  the training's start or end, and genuinely null when unset. */
  applicationDeadline: string | null;
  startsAt: string | null;
  endsAt: string | null;
  specializationName: string | null;
  /** Whole days from starts_at to ends_at, or null when either is unset. */
  durationDays: number | null;
  remainingDays: number | null;
}

/* ---------------------------------------------------------------------------
 * Notifications section (`recent_notifications`)
 * ------------------------------------------------------------------------- */

/** The notification kinds the dashboard styles. The backend sends whatever the
 *  notifications table holds, so this is narrowed defensively in lib/normalize.ts
 *  and anything unknown degrades to "system". */
export type DashboardNotificationType = "application" | "certificate" | "system";

/** One notification row. `read` is the backend's `!empty(read_at)` boolean, and
 *  `createdAt` is ISO-8601 with an explicit offset. */
export interface DashboardNotification {
  id: number;
  type: DashboardNotificationType;
  title: string;
  body: string;
  read: boolean;
  createdAt: string;
}

/** The whole normalized dashboard, as the container receives it. */
export interface StudentDashboard {
  student: DashboardStudent;
  quickStats: DashboardQuickStats;
  activeTraining: DashboardActiveTraining | null;
  applications: DashboardApplicationsSnapshot;
  recentApplications: DashboardRecentApplication[];
  certificates: DashboardCertificatesSection;
  recommendedTrainings: DashboardRecommendedTraining[];
  notifications: DashboardNotification[];
}
