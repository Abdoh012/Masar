import type { Metadata } from "next";

import { MyProfileContainer } from "@/features/profiles";

export const metadata: Metadata = {
  title: "My Profile",
};

// Thin composition point: the page renders one feature container and owns no
// structure or copy of its own (architecture R1). No Suspense boundary yet —
// the container reads from constants rather than the API; add one with a
// page-shaped skeleton when the profile read lands.
export default function Page() {
  return (
    <main className="mx-auto flex w-full max-w-6xl flex-col gap-6 p-4 sm:p-6 lg:p-8">
      <MyProfileContainer />
    </main>
  );
}
