import { Loader2 } from "lucide-react";

export default function Loading() {
  return (
    <div
      role="status"
      aria-live="polite"
      className="flex min-h-[40vh] flex-col items-center justify-center gap-4 bg-background font-sans text-foreground"
    >
      <Loader2 className="size-8 animate-spin text-primary-text" strokeWidth={2} />
      <p className="text-sm font-medium text-muted-foreground">Loading listing</p>
    </div>
  );
}
