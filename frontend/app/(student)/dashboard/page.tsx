import type { Metadata } from "next";

import { StudentDashboard } from "@/features/dashboard";

export const metadata: Metadata = {
  title: "Dashboard",
};

// force-dynamic: the container fetches authenticated, per-student data during
// render — without opting out, `next build` would prerender the fetch without
// the user's cookies and fail. (The applications page doesn't need this: it
// reads searchParams, which makes it dynamic automatically.)
export const dynamic = "force-dynamic";

// Thin composition point: the page owns the shell and the spacing rhythm (32px
// between the page's bands, 24px of padding inside each card — DashboardSection)
// and nothing else. Every section, the data read behind them, and all copy live
// in the feature's container.
export default function Page() {
  return (
    <main className="mx-auto flex w-full max-w-6xl flex-col gap-8 p-4 sm:p-6 lg:p-8">
      <StudentDashboard />
    </main>
  );
}
