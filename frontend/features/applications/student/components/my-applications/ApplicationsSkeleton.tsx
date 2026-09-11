import { Skeleton } from "@/shared/components/ui/skeleton";

// ApplicationsSkeleton: section-shaped loading placeholder for the My
// Applications page, rendered by the shell's Suspense fallback (FR-028). It
// mirrors the real layout — header row (title + the All-view count chip), the
// bordered tab bar with pill tabs, and the two-per-row application card grid —
// so the swap into content doesn't shift the page. Server-rendered, no
// interactivity.
export function ApplicationsSkeleton() {
  return (
    <div className="flex flex-col gap-6">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <Skeleton className="h-8 w-48" />
        <Skeleton className="h-7 w-16 rounded-full" />
      </div>

      <div className="flex flex-nowrap items-center gap-1 overflow-x-auto rounded-lg border border-border bg-card p-1">
        {[0, 1, 2, 3, 4].map((i) => (
          <Skeleton
            key={i}
            className={`h-9 shrink-0 rounded-md ${i % 2 === 0 ? "w-20" : "w-28"}`}
          />
        ))}
      </div>

      <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
        {[0, 1, 2, 3].map((card) => (
          <div
            key={card}
            className="flex h-full flex-col gap-3 rounded-xl border border-border bg-card p-5"
          >
            <div className="flex flex-wrap items-center gap-2">
              <Skeleton className="h-5 w-20 rounded-full" />
              <Skeleton className="h-5 w-24 rounded-full" />
            </div>

            <div className="space-y-1.5">
              <Skeleton className="h-5 w-3/4" />
              <Skeleton className="h-4 w-1/2" />
            </div>

            <div className="flex flex-wrap items-center gap-x-3 gap-y-1">
              <Skeleton className="h-3 w-40" />
              <Skeleton className="h-5 w-16 rounded-md" />
            </div>

            <div className="mt-auto flex flex-wrap items-center gap-2 pt-1">
              <Skeleton className="h-8 w-24 rounded-md" />
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}