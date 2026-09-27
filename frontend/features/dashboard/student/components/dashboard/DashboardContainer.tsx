import { ActiveTraining, ApplicationsSnapshot } from "@/features/applications";
import { CertificatesSnapshot } from "@/features/certificates";
import { RecommendedListings } from "@/features/listings";
import { RecentNotifications } from "@/features/notifications";
import { ProfileHeader } from "@/features/profiles";

import { fetchStudentDashboard } from "../../api";
import {
  normalizeStudentDashboard,
  toInitials,
} from "../../lib/normalize";
import { DashboardStatsContainer } from "../stats-overview/DashboardStatsContainer";

// DashboardContainer: the student dashboard's single orchestrator. It issues the
// ONE read of GET /students/dashboard, normalizes it, and arranges the sections —
// it renders no section's internals, and no section fetches for itself.
//
// Why the composition lives here instead of the page: /dashboard was the one
// route assembling four-plus feature sections by hand, which broke the thin-shell
// rule the other student routes follow. Now the page renders only this container
// and every section keeps its own structure and copy in its own feature.
//
// The sibling sections come from their features' public indices rather than
// deep-imported — the same discipline the route pages follow, and the precedent
// SidebarBrand already set by reaching BrandMark through @/features/auth.
//
// A failed read throws so the route's error boundary renders rather than the
// page silently falling back to zeroes, which would read as "you have no
// applications" when the truth is the request failed.
//
// Layout: the identity hero leads, then Active Training, then the stats strip
// immediately above the Applications snapshot, which is full width — the four
// status tiles need a full row to stop reading as cramped. The certificates and
// notifications cards still share a row at the end.
export default async function DashboardContainer() {
  const res = await fetchStudentDashboard();

  if (!res.success) {
    throw new Error(res.error || "Unable to load the dashboard.");
  }

  const dashboard = normalizeStudentDashboard(res.data);
  const {
    student,
    quickStats,
    activeTraining,
    applications,
    recentApplications,
    certificates,
    recommendedTrainings,
    notifications,
  } = dashboard;

  // "Faculty · University" reads as one line only when the student set at least
  // one of them, so the parts are joined rather than interpolating nulls.
  const studies =
    [student.faculty, student.university].filter(Boolean).join(" · ") || null;

  return (
    <>
      <ProfileHeader
        student={{
          name: student.fullName,
          field: student.field,
          studies,
          initials: toInitials(student.fullName),
          completion: student.profileCompletion,
        }}
      />

      <ActiveTraining
        active={
          activeTraining
            ? {
                id: activeTraining.id,
                company: activeTraining.company ?? "",
                listingTitle: activeTraining.title,
                deliveryMode: activeTraining.mode,
                isPaid: activeTraining.isPaid,
                startsAt: activeTraining.startsAt,
                endsAt: activeTraining.endsAt,
                remainingDays: activeTraining.remainingDays,
              }
            : null
        }
      />

      <DashboardStatsContainer stats={quickStats} />

      <ApplicationsSnapshot
        counts={{
          total: applications.total,
          applied: applications.applied,
          accepted: applications.accepted,
          rejected: applications.rejected,
          withdrawn: applications.withdrawn,
        }}
        recent={recentApplications}
        total={applications.total}
        acceptanceRate={applications.acceptanceRate}
      />

      {/* Recommended trainings — full width: the rows read as a recommendation,
          not as a second browse grid. */}
      <RecommendedListings
        trainings={recommendedTrainings.map((training) => ({
          id: training.id,
          title: training.title,
          companyName: training.company,
          // The dashboard presenter names these the other way round from the
          // browse grid (`type` is the mode, `mode` is the format); the
          // normalizer already resolved both to their enums.
          mode: training.mode,
          format: training.format,
          isPaid: training.isPaid,
          price: training.price,
          trialDays: training.freeTrialDays,
          specializationName: training.specializationName,
          createdAt: training.createdAt ?? undefined,
          applicationDeadline: training.applicationDeadline,
          durationDays: training.durationDays,
        }))}
      />

      {/* `items-stretch` is grid's default, stated explicitly because these two
          cards are meant to share one height. Neither one scrolls: the
          certificates document centres itself in whatever height the row
          resolves to, and the notification rows absorb the slack between them,
          so whichever card is shorter fills rather than trailing off. */}
      <section className="grid grid-cols-1 items-stretch gap-6 lg:grid-cols-2">
        <CertificatesSnapshot
          counts={certificates.counts}
          recent={certificates.recent}
        />
        <RecentNotifications notifications={notifications} />
      </section>
    </>
  );
}
