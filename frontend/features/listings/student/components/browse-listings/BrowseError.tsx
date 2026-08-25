import { CircleAlert } from "lucide-react";

// BrowseError: inline error state when the browse API fetch fails.
export function BrowseError({ message }: { message: string }) {
  return (
    <div className="mt-6 flex flex-col items-center gap-3 rounded-2xl border border-border bg-card p-10 text-center">
      <span className="grid size-10 place-items-center rounded-full bg-error-bg text-error-fg">
        <CircleAlert className="size-5" strokeWidth={2} />
      </span>
      <p className="text-sm font-medium text-primary-text">
        Failed to load trainings
      </p>
      <p className="text-sm text-muted-foreground">{message}</p>
    </div>
  );
}
