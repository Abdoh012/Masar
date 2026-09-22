import { Skeleton } from "@/shared/components/ui/skeleton";

// CertificatesPageSkeleton: content-shaped loading placeholder for the
// certificates page, mirroring the real layout the Suspense boundary reveals —
// the PageHeader band, the three summary stat cards, the how-it-works panel,
// then the eligible + issued sections with their placeholder cards — so the
// swap into content doesn't shift the page. Same root spacing (space-y-8) and
// card paddings as CertificatePageContent's children.
export function CertificatesPageSkeleton() {
  return (
    <div className="flex flex-col gap-8">
      {/* PageHeader band */}
      <div className="rounded-2xl border border-border bg-card px-5 py-8 shadow-card sm:px-8 sm:py-10">
        <div className="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between sm:gap-8">
          <div className="flex flex-col gap-4 sm:flex-row sm:items-center">
            <Skeleton className="size-12 rounded-2xl sm:size-14" />
            <div className="space-y-2.5">
              <Skeleton className="h-3 w-24" />
              <Skeleton className="h-9 w-64 sm:h-10 sm:w-72" />
              <Skeleton className="h-4 w-80 max-w-full" />
            </div>
          </div>
          <Skeleton className="h-7 w-24 rounded-full" />
        </div>
      </div>

      {/* Summary stat cards */}
      <div className="grid grid-cols-1 gap-3 sm:grid-cols-3">
        {[0, 1, 2].map((i) => (
          <div
            key={i}
            className="flex items-center gap-4 rounded-2xl border border-border bg-card p-5 shadow-card"
          >
            <Skeleton className="size-11 rounded-xl" />
            <div className="space-y-2">
              <Skeleton className="h-6 w-12" />
              <Skeleton className="h-3.5 w-28" />
            </div>
          </div>
        ))}
      </div>

      {/* How certificates work */}
      <div className="rounded-2xl border border-border bg-card p-6 shadow-card">
        <Skeleton className="h-3.5 w-44" />
        <div className="mt-6 grid grid-cols-1 gap-10 sm:grid-cols-3 sm:gap-6">
          {[0, 1, 2].map((i) => (
            <div key={i} className="flex flex-col items-center gap-3 text-center">
              <Skeleton className="size-12 rounded-full" />
              <div className="space-y-2">
                <Skeleton className="h-4 w-24" />
                <Skeleton className="h-3 w-32" />
              </div>
            </div>
          ))}
        </div>
      </div>

      {/* Eligible to request */}
      <div className="space-y-4">
        <div className="flex items-center gap-2">
          <Skeleton className="size-6 rounded-full" />
          <Skeleton className="h-6 w-40" />
        </div>
        <div className="flex flex-col gap-3 rounded-xl border border-border bg-card p-5 sm:flex-row sm:items-center sm:justify-between">
          <div className="flex items-start gap-3 sm:items-center">
            <Skeleton className="size-10 rounded-full" />
            <div className="space-y-2">
              <Skeleton className="h-5 w-56" />
              <Skeleton className="h-4 w-36" />
              <Skeleton className="mt-1 h-3 w-32" />
            </div>
          </div>
          <div className="shrink-0 self-start sm:self-center">
            <Skeleton className="h-9 w-40 rounded-md" />
          </div>
        </div>
      </div>

      {/* Your certificates */}
      <div className="space-y-4">
        <div className="flex items-center gap-2">
          <Skeleton className="size-6 rounded-full" />
          <Skeleton className="h-6 w-40" />
        </div>

        {[0, 1].map((group) => (
          <div key={group} className="space-y-3">
            <div className="flex items-center gap-2">
              <Skeleton className="size-6 rounded-full" />
              <Skeleton className="h-5 w-44" />
            </div>
            <div className="flex flex-col gap-4 rounded-xl border border-border bg-card p-5 sm:flex-row sm:items-center sm:justify-between">
              <div className="flex items-start gap-3 sm:items-center">
                <Skeleton className="size-10 rounded-full" />
                <div className="min-w-0 space-y-2.5">
                  <Skeleton className="h-5 w-56" />
                  <Skeleton className="h-4 w-40" />
                  <Skeleton className="h-3 w-32" />
                  <Skeleton className="h-3 w-36" />
                </div>
              </div>
              <div className="flex shrink-0 items-center gap-2 self-start sm:self-center">
                <Skeleton className="h-9 w-32 rounded-md" />
                <Skeleton className="h-9 w-9 rounded-md" />
              </div>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}