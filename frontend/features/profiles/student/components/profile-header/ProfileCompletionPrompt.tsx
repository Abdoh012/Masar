import Link from "next/link";
import { ArrowRight } from "lucide-react";

// Kicks in only when the mock profile is flagged incomplete; real link + focus ring.
export function ProfileCompletionPrompt() {
  return (
    <Link
      href="/profile"
      className="inline-flex shrink-0 items-center gap-2 rounded-full bg-primary-tint px-4 py-2 text-sm font-medium text-primary-text transition-colors hover:bg-primary/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
    >
      Complete your profile
      <ArrowRight aria-hidden="true" className="size-4" />
    </Link>
  );
}