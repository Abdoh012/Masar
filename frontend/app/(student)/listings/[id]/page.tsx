import type { Metadata } from "next";

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
    <div className="p-8">
      <h1 className="font-sans text-xl font-semibold text-navy">Training Listing</h1>
      <p className="mt-1 text-sm text-mid">{id}</p>
    </div>
  );
}
