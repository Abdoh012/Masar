import type { Metadata } from "next";
import { Suspense } from "react";

import { TrainingApplicationContainer } from "@/features/applications";
import { SectionSkeleton } from "@/shared/components/loading/SectionSkeleton";

export const metadata: Metadata = {
  title: "Training Application",
};

// Thin composition point: the apply wizard lives in the applications feature;
// this shell just resolves the listing id and composes the wizard behind one
// Suspense boundary (architecture §3). Imports only from the feature index and
// shared loading.
export default async function Page({
  params,
}: {
  params: Promise<{ id: string }>;
}) {
  const { id } = await params;

  return (
    <main className="mx-auto flex w-full max-w-6xl flex-col gap-6 p-4 sm:p-6 lg:p-8">
      <Suspense fallback={<SectionSkeleton />}>
        <TrainingApplicationContainer listingId={id} />
      </Suspense>
    </main>
  );
}