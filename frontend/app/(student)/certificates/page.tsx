import type { Metadata } from "next";
import { Suspense } from "react";

import { MyCertificatesPage } from "@/features/certificates";
import { SectionSkeleton } from "@/shared/components/loading/SectionSkeleton";

export const metadata: Metadata = {
  title: "My Certificates",
};

// Thin composition point: single section behind one Suspense boundary with the
// shared section skeleton as its fallback (architecture §3, FR-028). The page
// itself owns all structure and copy; this shell imports only from the feature
// index and shared loading.
export default function Page() {
  return (
    <main className="mx-auto flex w-full max-w-6xl flex-col gap-6 p-4 sm:p-6 lg:p-8">
      <Suspense fallback={<SectionSkeleton />}>
        <MyCertificatesPage />
      </Suspense>
    </main>
  );
}