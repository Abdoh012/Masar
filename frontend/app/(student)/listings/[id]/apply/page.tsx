import type { Metadata } from "next";
import { notFound } from "next/navigation";
import { Suspense } from "react";

import { TrainingApplicationContainer } from "@/features/applications";
import { fetchTrainingDetails } from "@/features/listings";
import { SectionSkeleton } from "@/shared/components/loading/SectionSkeleton";

export const metadata: Metadata = {
  title: "Training Application",
};

// Resolve the real training the student is applying to (from the URL id) so the
// wizard's success panel shows its actual title/company. The listings api throws
// the HTTP status as `Error.message`; a 404 becomes a not-found, anything else
// surfaces to the root error boundary.
async function resolveTraining(id: string) {
  try {
    const { data } = await fetchTrainingDetails(id);
    return {
      title: typeof data.title === "string" ? data.title : "",
      companyName: typeof data.company_name === "string" ? data.company_name : "",
    };
  } catch (error) {
    if (error instanceof Error && error.message === "404") {
      notFound();
    }
    throw error;
  }
}

// Thin composition point: the apply wizard lives in the applications feature;
// this shell resolves the listing id into the real training and composes the
// wizard behind one Suspense boundary (architecture §3). Imports only from the
// feature indexes and shared loading.
export default async function Page({
  params,
}: {
  params: Promise<{ id: string }>;
}) {
  const { id } = await params;
  const training = await resolveTraining(id);

  return (
    <main className="mx-auto flex w-full max-w-6xl flex-col gap-6 p-4 sm:p-6 lg:p-8">
      <Suspense fallback={<SectionSkeleton />}>
        <TrainingApplicationContainer
          listingId={id}
          listingTitle={training.title}
          companyName={training.companyName}
        />
      </Suspense>
    </main>
  );
}