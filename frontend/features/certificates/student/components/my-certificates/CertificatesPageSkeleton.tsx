import { Skeleton } from "@/shared/components/ui/skeleton";

// CertificatesPageSkeleton: content-shaped loading placeholder for the
// certificates page — header bars, an intro-panel block, then two card-row
// groups — matching the page layout a Suspense boundary reveals around it.
export function CertificatesPageSkeleton() {
  return (
    <div className="flex flex-col gap-6">
      <div className="flex items-center justify-between gap-4">
        <div className="space-y-2">
          <Skeleton className="h-3 w-24" />
          <Skeleton className="h-7 w-56" />
          <Skeleton className="h-4 w-80 max-w-full" />
        </div>
        <Skeleton className="h-7 w-24" />
      </div>

      <Skeleton className="h-32 w-full rounded-2xl" />

      <div className="space-y-4">
        <Skeleton className="h-6 w-40" />
        {[0, 1].map((i) => (
          <Skeleton key={i} className="h-24 w-full rounded-xl" />
        ))}
      </div>

      <div className="space-y-4">
        <Skeleton className="h-6 w-40" />
        {[0, 1, 2].map((i) => (
          <Skeleton key={i} className="h-28 w-full rounded-xl" />
        ))}
      </div>
    </div>
  );
}
