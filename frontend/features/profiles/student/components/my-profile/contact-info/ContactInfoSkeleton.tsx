import { Skeleton } from "@/shared/components/ui/skeleton";

// ContactInfoSkeleton: the contact-info section's Suspense fallback. Mirrors
// the read-only card — title, description, then the field grid — so the swap
// doesn't shift the page when the contact read resolves.
export function ContactInfoSkeleton() {
  return (
    <div className="rounded-2xl border border-border bg-card p-5 shadow-card sm:p-6">
      <Skeleton className="h-4 w-40" />
      <Skeleton className="mt-2 h-3 w-72 max-w-full" />
      <div className="mt-5 grid gap-4 sm:grid-cols-2">
        {[0, 1, 2, 3, 4].map((field) => (
          <div
            key={field}
            className="rounded-lg border border-border bg-background p-3"
          >
            <div className="flex items-start gap-3">
              <Skeleton className="size-9 shrink-0 rounded-lg" />
              <div className="min-w-0 flex-1 space-y-2">
                <Skeleton className="h-3.5 w-24" />
                <Skeleton className="h-3 w-full" />
              </div>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}
