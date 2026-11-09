import type { Metadata } from "next";

import { MyProfileContainer } from "@/features/profiles";

export const metadata: Metadata = {
  title: "My Profile",
};

// Per-user page: the identity hero reads the student's own profile header
// during render, so the route is always rendered dynamically.
export const dynamic = "force-dynamic";

export default function Page() {
  return (
    <main className="mx-auto flex w-full max-w-6xl flex-col gap-6 p-4 sm:p-6 lg:p-8">
      <MyProfileContainer />
    </main>
  );
}
