import Link from "next/link";
import { ArrowRight } from "lucide-react";

import { Button } from "@/shared/components/ui/button";

import {
  PROFILE_COMPLETION_COPY,
  PROFILE_COMPLETION_CTA,
} from "./constants";
import { ProfileCompletionMeter } from "./ProfileCompletionMeter";

// The completion nudge: the backend's percentage, its meter, one line of copy
// that reflects the state, and the "Complete your profile" CTA. The action is
// the same at every percentage — only the wording around it changes, so a
// student who is already complete isn't told they're behind.
export function ProfileCompletionPrompt({ percent }: { percent: number }) {
  const isComplete = percent >= 100;

  return (
    // `lg:` rather than `sm:` to match the breakpoint at which the header
    // actually goes side-by-side. Left at `sm:` this would shrink to its
    // content width and right-align while the card above it was still stacked.
    <div className="flex w-full shrink-0 flex-col items-start gap-3 lg:w-auto lg:items-end">
      <ProfileCompletionMeter percent={percent} />

      <p className="max-w-xs text-xs leading-relaxed text-muted-foreground">
        {isComplete
          ? PROFILE_COMPLETION_COPY.complete()
          : PROFILE_COMPLETION_COPY.incomplete(percent)}
      </p>

      <Button asChild className="shrink-0 gap-2 rounded-full">
        <Link href="/profile">
          {PROFILE_COMPLETION_CTA.label}
          <ArrowRight aria-hidden="true" className="size-4" />
        </Link>
      </Button>
    </div>
  );
}
