import { Skeleton } from "@/shared/components/ui/skeleton";

// ProfileViewsSkeleton: the profile-views section's Suspense fallback. Mirrors
// the real panel — eyebrow + description, the count band skeleton, then a few
// viewer-row skeletons — so the swap doesn't shift the page when the read
// resolves.
export function ProfileViewsSkeleton() {
  return (
    <div className="rounded-2xl border border-border bg-card p-5 shadow-card sm:p-6">
      <Skeleton className="h-4 w-40" />
      <Skeleton className="mt-2 h-3 w-72 max-w-full" />

      <div className="mt-5 grid gap-4 lg:grid-cols-2">
        <div className="flex items-center gap-4">
          <Skeleton className="size-11 shrink-0 rounded-xl" />
          <div className="min-w-0 space-y-1.5">
            <Skeleton className="h-8 sm:h-10 w-20 sm:w-24" />
            <Skeleton className="mt-1.5 h-4 w-48" />
          </div>
        </div>

        {[0, 1, 2].map((row) => (
          <div
            key={row}
            className="flex items-center gap-3 rounded-lg border border-border bg-card p-3"
          >
            <Skeleton className="size-9 shrink-0 rounded-full" />
            <div className="min-w-0 flex-1 space-y-1">
              <Skeleton className="h-4 w-24" />
              <Skeleton className="h-3 w-32" />
            </div>
            <Skeleton className="shrink-0 h-4 w-24" />
          </div>
        ))}
      </div>
    </div>
  );
}
