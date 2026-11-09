import { Skeleton } from "@/shared/components/ui/skeleton";

// IdentitySkeleton: the hero card's Suspense fallback. Mirrors IdentityHero's
// shape (circle + action row, then the name/badge/meta lines) so the swap
// doesn't shift the page when the profile-header read resolves.
export function IdentitySkeleton() {
  return (
    <div className="relative overflow-hidden rounded-2xl border border-border bg-card p-5 shadow-card sm:p-6">
      <div className="relative flex flex-col items-center gap-5 sm:flex-row sm:gap-6">
        <div className="flex shrink-0 flex-col items-center gap-2.5">
          <Skeleton className="size-24 rounded-full sm:size-28" />
          <Skeleton className="h-8 w-32" />
        </div>

        <div className="w-full min-w-0 space-y-3">
          <Skeleton className="mx-auto h-7 w-48 sm:mx-0" />
          <Skeleton className="mx-auto h-5 w-36 sm:mx-0" />
          <Skeleton className="mx-auto h-4 w-64 sm:mx-0" />
          <Skeleton className="mx-auto h-3 w-40 sm:mx-0" />
        </div>
      </div>
    </div>
  );
}
