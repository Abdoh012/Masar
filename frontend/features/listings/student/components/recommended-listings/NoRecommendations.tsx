import { Compass } from "lucide-react";

import { RECOMMENDED_EMPTY } from "./constants";

// Empty state for the recommended-trainings section: the backend found no
// training matching this student's specialization, which usually means the
// profile is still missing the field and skills that drive the match.
export function NoRecommendations() {
  return (
    <div className="mt-5 flex flex-col items-center gap-2 rounded-xl border border-dashed border-border bg-background px-4 py-8 text-center">
      <Compass aria-hidden="true" className="size-6 text-muted-foreground" />
      <p className="text-sm font-semibold text-foreground">
        {RECOMMENDED_EMPTY.title}
      </p>
      <p className="max-w-xs text-xs text-muted-foreground">
        {RECOMMENDED_EMPTY.message}
      </p>
    </div>
  );
}
