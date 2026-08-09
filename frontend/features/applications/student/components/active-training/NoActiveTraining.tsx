import Link from "next/link";
import { GraduationCap } from "lucide-react";

import { Button } from "@/shared/components/ui/button";

// Empty state for the Active training section — points the student to /listings.
export function NoActiveTraining() {
  return (
    <div className="mt-4 flex flex-col items-center gap-3 rounded-xl border border-dashed border-border bg-background px-4 py-8 text-center">
      {/* Icon */}
      <span className="flex size-10 items-center justify-center rounded-full bg-neutral-badge-bg text-neutral-badge-fg">
        <GraduationCap aria-hidden="true" className="size-5" />
      </span>

      {/* Text */}
      <div>
        <p className="text-sm font-semibold text-foreground">
          No active training
        </p>
        <p className="mt-0.5 text-xs text-muted-foreground">
          When you&apos;re accepted, your training will show up here.
        </p>
      </div>

      {/* Browse trainings */}
      <Button asChild size="sm">
        <Link href="/listings">Browse trainings</Link>
      </Button>
    </div>
  );
}
