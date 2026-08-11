import { cn } from "@/shared/lib/utils";

interface BrandMarkProps {
  // "navy" — the brand mark sits on the navy panel (white wordmark).
  // "paper" — the brand mark sits on a light surface (navy wordmark).
  tone?: "navy" | "paper";
  // "lg" — hero lockup (seal over wordmark). "sm" — compact header lockup.
  size?: "sm" | "lg";
  layout?: "vertical" | "horizontal";
  // Seal-only treatment (no wordmark, no gap) — used by the sidebar's
  // collapsed rail where only the mark fits.
  markOnly?: boolean;
  className?: string;
}

// BrandMark: the Masar seal + wordmark as a single lockup. The seal SVG
// matches masar-identity.html's hero seal exactly (gold outer ring, ring,
// and check). Rendered where the brand appears in the auth area; promote
// to top-level shared/ when a second feature needs it (R7).
export function BrandMark({
  tone = "navy",
  size = "lg",
  layout = "vertical",
  markOnly = false,
  className,
}: BrandMarkProps) {
  const onPaper = tone === "paper";
  const wordmarkColor = onPaper ? "text-primary-text" : "text-neutral-50";
  const innerRing = onPaper ? "stroke-primary-500/35" : "stroke-neutral-50/35";

  return (
    <div
      className={cn(
        "flex items-center",
        layout === "vertical" ? "flex-col gap-2" : "gap-3",
        className,
      )}
    >
      <svg
        viewBox="0 0 64 64"
        fill="none"
        aria-hidden="true"
        className={cn("shrink-0", size === "lg" ? "size-14" : "size-9")}
      >
        <circle cx="32" cy="32" r="28" stroke="#e8a33d" strokeWidth="2" />
        <circle cx="32" cy="32" r="21" className={innerRing} strokeWidth="1" />
        <path
          d="M22 33l7 7 13-15"
          stroke="#e8a33d"
          strokeWidth="3"
          strokeLinecap="round"
          strokeLinejoin="round"
        />
      </svg>
      {markOnly ? null : (
        <span
          className={cn(
            "font-sans font-semibold leading-none tracking-tight",
            wordmarkColor,
            size === "lg" ? "text-5xl" : "text-2xl",
          )}
        >
          Mas<span className="text-secondary-500">ar</span>
        </span>
      )}
    </div>
  );
}
