import { BROWSE_EMPTY_STATE } from "./constants";

// BrowseEmptyState: dashed panel shown when no trainings match the active filters.
export function BrowseEmptyState() {
  return (
    <div className="mt-6 rounded-2xl border border-dashed border-border bg-card p-10 text-center">
      <p className="text-sm font-medium text-primary-text">
        {BROWSE_EMPTY_STATE.title}
      </p>
      <p className="mt-1 text-sm text-muted-foreground">
        {BROWSE_EMPTY_STATE.message}
      </p>
    </div>
  );
}
