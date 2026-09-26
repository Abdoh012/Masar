import Link from "next/link";
import { ArrowRight } from "lucide-react";

import { Button } from "@/shared/components/ui/button";

import { PROFILE_COMPLETION_CTA } from "./constants";

// Kicks in only when the mock profile is flagged incomplete; real link + focus ring.
export function ProfileCompletionPrompt() {
  return (
    <Button asChild className="shrink-0 gap-2 rounded-full">
      <Link href="/profile">
        {PROFILE_COMPLETION_CTA.label}
        <ArrowRight aria-hidden="true" className="size-4" />
      </Link>
    </Button>
  );
}
