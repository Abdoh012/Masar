import type { Metadata } from "next";

import { ActiveTraining, ApplicationsSnapshot } from "@/features/applications";
import { CertificatesSnapshot } from "@/features/certificates";
import { RecommendedListings } from "@/features/listings";
import { RecentNotifications } from "@/features/notifications";
import { ProfileHeader } from "@/features/profiles";

export const metadata: Metadata = {
  title: "Dashboard",
};

// Pure composition point: six sections in spec order, header full-width above a
// two-column desktop grid that collapses to one column on tablet/mobile.
export default function Page() {
  return (
    <main className="mx-auto flex w-full max-w-6xl flex-col gap-6 p-4 sm:p-6 lg:p-8">
      <ProfileHeader />

      <section className="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {/* Active Training */}
        <ActiveTraining />

        {/* Applications Snapshot */}
        <ApplicationsSnapshot />

        {/* Certificates Snapshot */}
        <CertificatesSnapshot />

        {/* Recommended Listings */}
        <RecommendedListings />

        {/* Recent Notifications */}
        <div className="lg:col-span-2">
          <RecentNotifications />
        </div>
      </section>
    </main>
  );
}
