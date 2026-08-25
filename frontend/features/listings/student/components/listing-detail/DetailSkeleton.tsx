import { Skeleton } from "@/shared/components/ui/skeleton";

// DetailSkeleton: loading placeholder shaped like the listing detail card
// (badges + heading + meta grid + description block).
export function DetailSkeleton() {
  return (
    <article className="space-y-8">
      <div className="space-y-4 rounded-2xl border border-border bg-card p-6">
        <div className="flex gap-2">
          <Skeleton className="h-6 w-20 rounded-full" />
          <Skeleton className="h-6 w-16 rounded-full" />
        </div>

        <Skeleton className="h-8 w-2/3" />

        <div className="grid gap-3 sm:grid-cols-2">
          {Array.from({ length: 4 }).map((_, i) => (
            <div key={i} className="flex items-center gap-2">
              <Skeleton className="size-4 shrink-0" />
              <Skeleton className="h-5 w-32" />
            </div>
          ))}
        </div>

        <div className="space-y-2">
          <Skeleton className="h-4 w-full" />
          <Skeleton className="h-4 w-full" />
          <Skeleton className="h-4 w-3/4" />
        </div>
      </div>

      <div className="rounded-2xl border border-border bg-card p-6">
        <Skeleton className="h-12 w-full rounded-xl" />
      </div>
    </article>
  );
}
