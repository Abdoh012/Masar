import { Skeleton } from "@/shared/components/ui/skeleton";

// AcademicStatusSkeleton: the academic-status section's Suspense fallback.
// Mirrors the real card — eyebrow + description, then the segmented control,
// the graduate-only date field and certificate area — so the swap doesn't
// shift the page when the read resolves.
export function AcademicStatusSkeleton() {
  return (
    <div className="rounded-2xl border border-border bg-card p-5 shadow-card sm:p-6">
      <div className="space-y-1.5">
        <Skeleton className="h-4 w-32" />
        <Skeleton className="mt-1 h-3 w-3/4" />
      </div>

      <div className="mt-5 space-y-5">
        <Skeleton className="h-10 w-1/3" />

        <MotionPlaceholder />
      </div>
    </div>
  );
}

function MotionPlaceholder() {
  return (
    <div className="space-y-5">
      <div>
        <Skeleton className="h-10 w-1/2" />
        <Skeleton className="mt-1.5 h-3 w-full" />
      </div>

      <div className="space-y-2">
        <Skeleton className="h-9 w-1/3" />
        <Skeleton className="mt-1 h-8 w-1/3" />
      </div>
    </div>
  );
}
