import { Skeleton } from "@/shared/components/ui/skeleton";

// SectionSkeleton: the shared single-section loading placeholder rendered by a
// route's Suspense fallback. Shaped like the My Applications section (header
// bar + tab strip + card rows) but generic enough for any single-section page.
// Server-rendered — no interactivity.
export function SectionSkeleton() {
  return (
    <div className="flex flex-col gap-6">
      <div className="flex items-center justify-between gap-4">
        <Skeleton className="h-7 w-44" />
        <Skeleton className="h-6 w-20" />
      </div>

      <Skeleton className="h-10 w-full rounded-lg" />

      <div className="space-y-4">
        {[0, 1, 2].map((i) => (
          <div
            key={i}
            className="flex flex-col gap-4 rounded-xl border border-border bg-card p-5 sm:flex-row sm:items-center sm:justify-between"
          >
            <div className="space-y-2">
              <Skeleton className="h-4 w-28" />
              <Skeleton className="h-5 w-52" />
              <Skeleton className="h-3 w-32" />
            </div>
            <Skeleton className="h-9 w-28" />
          </div>
        ))}
      </div>
    </div>
  );
}