import { Skeleton } from "@/shared/components/ui/skeleton";

// DetailSkeleton: loading placeholder shaped like the listing detail card
// (badges row, title, meta grid, description block, apply CTA, back button).
// Server leaf rendered by the [id] route loading.tsx so the transition keeps
// the detail page's shape instead of collapsing to a spinner.
export function DetailSkeleton() {
  return (
    <article className="space-y-8" aria-hidden="true">
      <div className="space-y-4 rounded-2xl border border-border bg-card p-6">
        <div className="flex flex-wrap items-center gap-2">
          <Skeleton className="h-7 w-20 rounded-full" />
          <Skeleton className="h-7 w-24 rounded-full" />
          <Skeleton className="ml-auto size-8 rounded-lg" />
        </div>

        <Skeleton className="h-8 w-3/4 max-w-sm" />

        <div className="grid gap-3 sm:grid-cols-2">
          {[0, 1, 2, 3].map((i) => (
            <div key={i} className="flex items-center gap-2">
              <Skeleton className="size-4 rounded-full" />
              <Skeleton className="h-3.5 w-32" />
            </div>
          ))}
        </div>

        <div className="space-y-2 pt-1">
          <Skeleton className="h-3.5 w-full" />
          <Skeleton className="h-3.5 w-full" />
          <Skeleton className="h-3.5 w-2/3" />
        </div>
      </div>

      <div className="rounded-2xl border border-border bg-card p-6">
        <Skeleton className="h-11 w-full rounded-lg" />
      </div>

      <Skeleton className="h-10 w-28 rounded-md" />
    </article>
  );
}