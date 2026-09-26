import { Check } from "lucide-react";

interface VerifiedSealProps {
  className?: string;
}

// Leaf: the verification mark that sits on a presented certificate. Gold, not
// green — the design system's seal/stamp (approval, certificate, confirmed
// hire) is the gold seal, so the badge reads as part of the document rather
// than as a generic success state. Decorative: the line beneath the document
// already announces "Verified certificate" to assistive tech.
export function VerifiedSeal({ className }: VerifiedSealProps) {
  return (
    <span
      aria-hidden="true"
      className={
        "flex size-8 items-center justify-center rounded-full " +
        "bg-secondary text-secondary-foreground shadow-card-md ring-4 ring-card " +
        (className ?? "")
      }
    >
      <Check className="size-4" />
    </span>
  );
}
