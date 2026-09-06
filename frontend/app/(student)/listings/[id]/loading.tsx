import { DetailSkeleton } from "@/features/listings";

export default function Loading() {
  return (
    <div className="min-h-[calc(100dvh-3.5rem)] bg-[#FAF7F1] p-8">
      <h1 className="sr-only">Training Listing</h1>
      <div className="mx-auto max-w-3xl">
        <DetailSkeleton />
      </div>
    </div>
  );
}