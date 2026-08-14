import type { Metadata } from "next";

import { ListingForm } from "@/features/listings";

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
    <div className="p-8">
      <h1 className="font-sans text-xl font-semibold text-foreground">Edit Listing</h1>
      <div className="mt-6 max-w-2xl">
        <ListingForm mode="edit" listingId={id} />
      </div>
    </div>
  );
}
