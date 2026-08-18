import type { Metadata } from "next";

import { ListingFormContainer } from "@/features/listings";

interface PageProps {
  params: Promise<{ id: string }>;
}

export async function generateMetadata({ params }: PageProps): Promise<Metadata> {
  const { id } = await params;
  return { title: `Edit Listing — ${id}` };
}

export default async function Page({ params }: PageProps) {
  const { id } = await params;
  return (
    <div className="min-h-[calc(100dvh-3.5rem)] bg-[#FAF7F1] p-8">
      <h1 className="font-sans text-xl font-semibold text-foreground">Edit Listing</h1>
      <div className="mt-6 max-w-2xl">
        <ListingFormContainer mode="edit" listingId={id} />
      </div>
    </div>
  );
}
