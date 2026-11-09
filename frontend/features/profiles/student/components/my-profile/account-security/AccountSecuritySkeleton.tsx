import { Skeleton } from "@/shared/components/ui/skeleton";

// AccountSecuritySkeleton: the account-security section's Suspense fallback.
// Mirrors the real card — eyebrow + description, then the two inset panels — so
// the swap doesn't shift the page when the full-name read resolves.
export function AccountSecuritySkeleton() {
  return (
    <div className="rounded-2xl border border-border bg-card p-5 shadow-card sm:p-6">
      <Skeleton className="h-4 w-40" />
      <Skeleton className="mt-2 h-3 w-72 max-w-full" />

      <div className="mt-5 grid gap-4 lg:grid-cols-2">
        {[0, 1].map((panel) => (
          <div
            key={panel}
            className="rounded-xl border border-border bg-background p-4 sm:p-5"
          >
            <div className="flex items-start gap-3">
              <Skeleton className="size-9 shrink-0 rounded-lg" />

              <div className="min-w-0 flex-1 space-y-2">
                <Skeleton className="h-3.5 w-24" />
                <Skeleton className="h-3 w-full" />
              </div>
            </div>

            <Skeleton className="mt-4 h-10 w-full" />
            <Skeleton className="mt-3 h-8 w-28" />
          </div>
        ))}
      </div>
    </div>
  );
}
