import type { Metadata } from "next";

import { ListingDetailContainer } from "@/features/listings";

interface PageProps {
  params: Promise<{ id: string }>;
}

export async function generateMetadata({ params }: PageProps): Promise<Metadata> {
  const { id } = await params;
  return { title: `Training Listing — ${id}` };
}

export default async function Page({ params }: PageProps) {
  const { id } = await params;
  return (
    <div className="min-h-[calc(100dvh-3.5rem)] bg-[#FAF7F1] p-8">
      <h1 className="sr-only">Training Listing</h1>
      <div className="mx-auto max-w-3xl">
        <ListingDetailContainer id={id} />
      </div>
    </div>
  );
}
