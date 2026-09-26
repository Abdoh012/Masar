import type { Metadata } from "next";

import { ActiveTraining, ApplicationsSnapshot } from "@/features/applications";
import { CertificatesSnapshot } from "@/features/certificates";
import { RecommendedListings } from "@/features/listings";
import { RecentNotifications } from "@/features/notifications";
import { ProfileHeader } from "@/features/profiles";

export const metadata: Metadata = {
  title: "Dashboard",
};

// Pure composition point. Spacing runs on a 24/32 rhythm: 32px between the
// page's bands, 24px between cards inside a band, and 24px of padding inside
// each card (DashboardSection). The two grids pair up so no cell is left
// empty, and both collapse to a single column below `lg`.
export default function Page() {
  return (
    <main className="mx-auto flex w-full max-w-6xl flex-col gap-8 p-4 sm:p-6 lg:p-8">
      <ProfileHeader />

      <section className="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {/* Active Training */}
        <ActiveTraining />

        {/* Applications Snapshot */}
        <ApplicationsSnapshot />
      </section>

      {/* Recommended Listings — full width: the two rows read as a
          recommendation, not as a second browse grid. */}
      <RecommendedListings />

      {/* `items-stretch` is grid's default, stated explicitly because these two
          cards are meant to share one height: the notifications feed is capped
          so the pair stays bounded, and the stretch makes the certificates card
          match it rather than ending short. */}
      <section className="grid grid-cols-1 items-stretch gap-6 lg:grid-cols-2">
        {/* Certificates Snapshot */}
        <CertificatesSnapshot />

        {/* Recent Notifications */}
        <RecentNotifications />
      </section>
    </main>
  );
}
