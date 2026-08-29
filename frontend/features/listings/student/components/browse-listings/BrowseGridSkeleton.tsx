// BrowseGridSkeleton: loading placeholder shaped like the browse card grid.
export function BrowseGridSkeleton() {
  return (
    <div className="mt-6 grid gap-5 sm:grid-cols-2 xl:grid-cols-2">
      {Array.from({ length: 10 }).map((_, i) => (
        <div
          key={i}
          className="h-64 animate-pulse rounded-2xl border border-border bg-card"
        />
      ))}
    </div>
  );
}
