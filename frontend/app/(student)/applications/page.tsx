import type { Metadata } from "next";
import { Suspense } from "react";

import { ApplicationsSkeleton, MyApplicationsPage } from "@/features/applications";

export const metadata: Metadata = {
  title: "My Applications",
};

interface PageProps {
  searchParams: Promise<Record<string, string | string[] | undefined>>;
}

// Thin composition point: single section behind one Suspense boundary with the
// section-shaped skeleton as its fallback (architecture §3, FR-028). The page
// itself owns all structure and copy; this shell imports only from the feature
// index and unwraps searchParams for the container (thin-shell rule — mirrors
// app/(student)/listings/page.tsx).
export default async function Page({ searchParams }: PageProps) {
  const sp = await searchParams;

  return (
    <main className="mx-auto flex w-full max-w-6xl flex-col gap-6 p-4 sm:p-6 lg:p-8">
      <Suspense fallback={<ApplicationsSkeleton />}>
        <MyApplicationsPage searchParams={sp} />
      </Suspense>
    </main>
  );
}