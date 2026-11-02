// API → UI mapping for the student dashboard (role-level normalize, mirroring
// features/applications/student/lib/normalize.ts). The backend presenter output
// is snake_case with nullable fields; it is coerced to the role types here, once,
// so the container and the card leaves stay free of API-shape knowledge.
import type {
  DashboardActiveTraining,
  DashboardApplicationStatus,
  DashboardApplicationsSnapshot,
  DashboardCertificateCounts,
  DashboardCertificateRecord,
  DashboardNotification,
  DashboardNotificationType,
  DashboardQuickStats,
  DashboardRecommendedTraining,
  DashboardRecentApplication,
  DashboardStudent,
  DashboardTrainingFormat,
  DashboardTrainingMode,
  StudentDashboard,
} from "@/features/dashboard/student/types";

type UnknownRecord = Record<string, unknown>;

const toStr = (value: unknown): string | null =>
  typeof value === "string" && value.length > 0 ? value : null;

/** Counts are plain ints on the wire; coerce defensively so a missing key reads
 *  as 0 rather than NaN reaching a tile. */
const toCount = (value: unknown): number => {
  const parsed = Number(value);
  return Number.isFinite(parsed) ? parsed : 0;
};

const toBool = (value: unknown): boolean => value === true || value === 1;

/** The backend already guarantees an int in 0..100 (its own regression suite
 *  asserts the range), so the only job here is to refuse a missing/non-numeric
 *  value rather than let `undefined` reach the header. */
function toPercentage(value: unknown): number {
  const parsed = Number(value);
  if (!Number.isFinite(parsed)) return 0;
  return Math.min(100, Math.max(0, Math.round(parsed)));
}

/** Maps `student` — including the backend-computed completion percentage, which
 *  is taken as-is (never derived here). */
export function normalizeDashboardStudent(raw: unknown): DashboardStudent {
  const student = (raw ?? {}) as UnknownRecord;
  const completion = (student.profile_completion ?? {}) as UnknownRecord;

  return {
    id: toCount(student.id),
    fullName: toStr(student.full_name) ?? "",
    field: toStr(student.field),
    faculty: toStr(student.faculty),
    university: toStr(student.university),
    profileImageFileId:
      student.profile_image_file_id == null
        ? null
        : toCount(student.profile_image_file_id),
    profileCompletion: toPercentage(completion.percentage),
  };
}

export function normalizeDashboardQuickStats(
  raw: unknown,
): DashboardQuickStats {
  const stats = (raw ?? {}) as UnknownRecord;

  return {
    activeTrainings: toCount(stats.active_trainings),
    applications: toCount(stats.applications),
    issuedCertificates: toCount(stats.issued_certificates),
  };
}

/** The `mode` column is an enum of exactly these three delivery formats, so an
 *  unrecognised value means the training stored something the UI has no label
 *  for. It degrades to `in_person` (the neutral "in person" reading) rather than
 *  leaking a raw string into the mode pill. */
const DELIVERY_MODES = ["in_person", "remote", "hybrid"] as const;

export type DashboardDeliveryMode = (typeof DELIVERY_MODES)[number];

function toDeliveryMode(value: unknown): DashboardDeliveryMode {
  const mode = toStr(value);
  return DELIVERY_MODES.find((known) => known === mode) ?? "in_person";
}

/** Two-letter monogram for the identity avatar, from the student's own name.
 *  Derived here (rather than in the header) so the API→UI mapping stays the one
 *  place presentation data is produced. */
export function toInitials(name: string): string {
  const parts = name.trim().split(/\s+/).filter(Boolean);
  if (parts.length === 0) return "?";
  if (parts.length === 1) return parts[0].charAt(0).toUpperCase();
  return (parts[0].charAt(0) + parts[parts.length - 1].charAt(0)).toUpperCase();
}

/** Maps `active_training`, or null when the student has no accepted
 *  application. The ISO strings keep their UTC offset untouched — see
 *  applications/student/lib/training-progress.ts, which parses them. */
export function normalizeDashboardActiveTraining(
  raw: unknown,
): DashboardActiveTraining | null {
  if (!raw || typeof raw !== "object" || Array.isArray(raw)) return null;
  const training = raw as UnknownRecord;

  return {
    id: toCount(training.id),
    title: toStr(training.title) ?? "",
    company: toStr(training.company),
    type: toStr(training.type) ?? "",
    mode: toDeliveryMode(training.mode),
    isPaid: toBool(training.is_paid),
    trialDays:
      training.trial_days == null ? null : toCount(training.trial_days),
    startsAt: toStr(training.starts_at),
    endsAt: toStr(training.ends_at),
    remainingDays:
      training.remaining_days == null ? null : toCount(training.remaining_days),
  };
}

export function normalizeDashboardApplications(
  raw: unknown,
): DashboardApplicationsSnapshot {
  const snapshot = (raw ?? {}) as UnknownRecord;

  return {
    total: toCount(snapshot.total),
    applied: toCount(snapshot.applied),
    accepted: toCount(snapshot.accepted),
    rejected: toCount(snapshot.rejected),
    withdrawn: toCount(snapshot.withdrawn),
    acceptanceRate: toPercentage(snapshot.acceptance_rate),
  };
}

/** The four statuses the tiles and the recent rows share. Anything else (a
 *  status this app doesn't render) degrades to "Applied", matching how the
 *  applications layer treats an unrecognised card status. */
function toStatus(value: unknown): DashboardApplicationStatus {
  const status = toStr(value);
  return status === "Accepted" || status === "Rejected" || status === "Withdrawn"
    ? status
    : "Applied";
}

/** Each recent card carries only the timestamp of its own current state, so the
 *  snapshot needs the same status→date rule the My Applications cards use:
 *  the status's own action, falling back to applied_at (always present while the
 *  application is still in flight, absent once it has been actioned). */
function toLatestActionIso(
  card: UnknownRecord,
  status: DashboardApplicationStatus,
): string | null {
  const byStatus: Record<DashboardApplicationStatus, string> = {
    Applied: "applied_at",
    Accepted: "accepted_at",
    Rejected: "rejected_at",
    Withdrawn: "withdrawn_at",
  };

  return toStr(card[byStatus[status]]) ?? toStr(card.applied_at);
}

/** Maps the snapshot's recent[] preview — the same All-tab card DTO the My
 *  Applications page reads, capped at 3 by the backend. */
export function normalizeDashboardRecentApplications(
  raw: unknown,
): DashboardRecentApplication[] {
  if (!Array.isArray(raw)) return [];

  return raw
    .filter(
      (item): item is UnknownRecord =>
        !!item && typeof item === "object" && !Array.isArray(item),
    )
    .map((card) => {
      const status = toStatus(card.status);
      return {
        id: toCount(card.id),
        trainingId: toCount(card.training_id),
        listingTitle: toStr(card.training_title) ?? "Training",
        companyName: toStr(card.company_name) ?? "Company",
        status,
        dateOn: toLatestActionIso(card, status),
      };
    });
}

/** Filters a list-shaped API value down to its plain-object entries, so one
 *  malformed row can't take a whole section down with it. */
function toRecords(raw: unknown): UnknownRecord[] {
  if (!Array.isArray(raw)) return [];

  return raw.filter(
    (item): item is UnknownRecord =>
      !!item && typeof item === "object" && !Array.isArray(item),
  );
}

/* -------------------------------------------------------------------------
 * Certificates
 * ---------------------------------------------------------------------- */

export function normalizeDashboardCertificateCounts(
  raw: unknown,
): DashboardCertificateCounts {
  const counts = (raw ?? {}) as UnknownRecord;

  return {
    eligible: toCount(counts.eligible),
    pending: toCount(counts.pending),
    issued: toCount(counts.issued),
    revoked: toCount(counts.revoked),
  };
}

/** Maps one certificate record presenter. `student` is a nested block on the
 *  wire; the document only needs the name and the field, so it is flattened
 *  here rather than carried as a nested object the dashboard has no use for. */
export function normalizeDashboardCertificateRecord(
  raw: UnknownRecord,
): DashboardCertificateRecord {
  const student = (raw.student ?? {}) as UnknownRecord;

  return {
    id: toCount(raw.id),
    certificateNumber: toStr(raw.certificate_number),
    status: toStr(raw.status) ?? "pending",
    companyName: toStr(raw.company_name),
    trainingTitle: toStr(raw.training_title) ?? "Training",
    specializationName: toStr(raw.specialization_name),
    // Optional rather than nullable, to match the certificates page's own
    // record shape: the dashboard record is handed straight to that feature's
    // section, so the two have to stay interchangeable.
    grade: toStr(raw.grade) ?? undefined,
    gradeLabel: toStr(raw.grade_label) ?? undefined,
    issuedAt: toStr(raw.issued_at),
    studentName: toStr(student.full_name) ?? "",
    field: toStr(student.field) ?? toStr(raw.specialization_name),
  };
}

export function normalizeDashboardCertificates(
  raw: unknown,
): { counts: DashboardCertificateCounts; recent: DashboardCertificateRecord[] } {
  const section = (raw ?? {}) as UnknownRecord;

  return {
    counts: normalizeDashboardCertificateCounts(section),
    recent: toRecords(section.recent).map(
      normalizeDashboardCertificateRecord,
    ),
  };
}

/* -------------------------------------------------------------------------
 * Recommended trainings
 * ---------------------------------------------------------------------- */

const TRAINING_MODES: DashboardTrainingMode[] = [
  "observer",
  "hands_on",
  "project_based",
];

const TRAINING_FORMATS: DashboardTrainingFormat[] = [
  "in_person",
  "remote",
  "hybrid",
];

/** The presenter names these the other way round from the browse grid: the
 *  card's `type` is the participation mode and its `mode` is the delivery
 *  format. Both enums are closed, so an unrecognised value degrades rather
 *  than leaking a raw string into a badge or a meta line. */
function toTrainingMode(value: unknown): DashboardTrainingMode {
  const mode = toStr(value);
  return TRAINING_MODES.find((known) => known === mode) ?? "hands_on";
}

function toTrainingFormat(value: unknown): DashboardTrainingFormat {
  const format = toStr(value);
  return TRAINING_FORMATS.find((known) => known === format) ?? "in_person";
}

export function normalizeDashboardRecommendedTrainings(
  raw: unknown,
): DashboardRecommendedTraining[] {
  return toRecords(raw).map((item) => {
    // `specialization` arrives as a nested {id,name} object, not a string.
    const specialization = (item.specialization ?? {}) as UnknownRecord;

    return {
      id: toCount(item.id),
      title: toStr(item.title) ?? "Training",
      company: toStr(item.company),
      companyLogo: toStr(item.company_logo),
      mode: toTrainingMode(item.type),
      format: toTrainingFormat(item.mode),
      isPaid: toBool(item.is_paid),
      price: item.price == null ? null : toCount(item.price),
      freeTrialDays:
        item.free_trial_days == null ? null : toCount(item.free_trial_days),
      createdAt: toStr(item.created_at),
      applicationDeadline: toStr(item.application_deadline),
      startsAt: toStr(item.starts_at),
      endsAt: toStr(item.ends_at),
      specializationName: toStr(specialization.name),
      durationDays: item.duration == null ? null : toCount(item.duration),
      remainingDays:
        item.remaining_days == null ? null : toCount(item.remaining_days),
    };
  });
}

/* -------------------------------------------------------------------------
 * Notifications
 * ---------------------------------------------------------------------- */

const NOTIFICATION_TYPES: DashboardNotificationType[] = [
  "application",
  "certificate",
  "system",
];

/** The notifications table can grow kinds this app has no styling for, so an
 *  unknown type degrades to "system" — the neutral treatment — instead of
 *  breaking the icon/style lookup in the row. */
function toNotificationType(value: unknown): DashboardNotificationType {
  const type = toStr(value);
  return NOTIFICATION_TYPES.find((known) => known === type) ?? "system";
}

export function normalizeDashboardNotifications(
  raw: unknown,
): DashboardNotification[] {
  return toRecords(raw).map((item) => ({
    id: toCount(item.id),
    type: toNotificationType(item.type),
    title: toStr(item.title) ?? "",
    body: toStr(item.body) ?? "",
    read: toBool(item.read),
    createdAt: toStr(item.created_at) ?? "",
  }));
}

/** Normalizes the whole payload in one place so the container reads plain data
 *  and never has to guard a missing section. A 404 (`Student profile not
 *  found.`) reaches the caller as a failed response, not as an empty dashboard,
 *  because serverFetch does not return `data` on a non-OK response. */
export function normalizeStudentDashboard(raw: unknown): StudentDashboard {
  const body = (raw ?? {}) as UnknownRecord;
  const snapshot = (body.applications_snapshot ?? {}) as UnknownRecord;

  return {
    student: normalizeDashboardStudent(body.student),
    quickStats: normalizeDashboardQuickStats(body.quick_stats),
    activeTraining: normalizeDashboardActiveTraining(body.active_training),
    applications: normalizeDashboardApplications(snapshot),
    recentApplications: normalizeDashboardRecentApplications(snapshot.recent),
    certificates: normalizeDashboardCertificates(body.certificates),
    recommendedTrainings: normalizeDashboardRecommendedTrainings(
      body.recommended_trainings,
    ),
    notifications: normalizeDashboardNotifications(body.recent_notifications),
  };
}
