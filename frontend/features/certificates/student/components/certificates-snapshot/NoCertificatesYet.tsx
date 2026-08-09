import { ScrollText } from "lucide-react";

// Empty state for the Certificates section.
export function NoCertificatesYet() {
  return (
    <div className="mt-4 flex flex-col items-center gap-2 rounded-xl border border-dashed border-border bg-background px-4 py-8 text-center">
      <ScrollText aria-hidden="true" className="size-6 text-muted-foreground" />
      <p className="text-sm font-semibold text-foreground">No certificates yet</p>
      <p className="text-xs text-muted-foreground">
        Verified certificates will appear here once you complete a training.
      </p>
    </div>
  );
}