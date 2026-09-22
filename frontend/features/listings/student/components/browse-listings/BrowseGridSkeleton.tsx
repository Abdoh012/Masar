import { Skeleton } from "@/shared/components/ui/skeleton";

// BrowseGridSkeleton: content-shaped loading placeholder for the browse grid,
// rendered by the container's Suspense fallback. It mirrors the real
// ListingCard layout — header row (category pill + save/logo), title, badges,
// company, description, meta + CTA — so the swap into content doesn't shift
// the page. Server leaf, no interactivity.
export function BrowseGridSkeleton() {
  return (
    <div className="mt-6 grid gap-5 sm:grid-cols-2 xl:grid-cols-2" aria-hidden="true">
      {[0, 1, 2, 3].map((i) => (
        <div
          key={i}
          className="flex h-full flex-col gap-3 rounded-2xl border border-border bg-card p-5 shadow-card"
        >
          <div className="flex items-center justify-between gap-3">
            <Skeleton className="h-6 w-20 rounded-full" />
            <div className="flex items-center gap-1">
              <Skeleton className="size-8 rounded-md" />
              <Skeleton className="size-12 rounded-xl" />
            </div>
          </div>

          <Skeleton className="h-5 w-3/4" />

          <div className="flex flex-wrap items-center gap-2">
            <Skeleton className="h-6 w-16 rounded-full" />
            <Skeleton className="h-6 w-24 rounded-full" />
          </div>

          <Skeleton className="h-3.5 w-48" />

          <div className="space-y-2">
            <Skeleton className="h-3.5 w-full" />
            <Skeleton className="h-3.5 w-full" />
            <Skeleton className="h-3.5 w-2/3" />
          </div>

          <ul className="flex flex-wrap gap-1.5">
            {[0, 1].map((s) => (
              <Skeleton key={s} className="h-6 w-16 rounded-md" />
            ))}
          </ul>

          <div className="mt-auto flex flex-col gap-3 pt-1">
            <div className="flex flex-wrap items-center gap-x-4 gap-y-1.5">
              <Skeleton className="h-3 w-20" />
              <Skeleton className="h-3 w-16" />
              <Skeleton className="h-3 w-24" />
            </div>
            <Skeleton className="h-4 w-24" />
          </div>
        </div>
      ))}
    </div>
  );
}
