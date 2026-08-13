import { cn } from "@/shared/lib/utils";
import Image from "next/image";

interface BrandMarkProps {
  // White backing behind the seal (the seal itself is always primary
  // navy — the backing gives it contrast on dark/navy surfaces).
  // "always" — chip in both themes (auth pages, sidebar rail).
  // "dark" — chip only in dark mode (public header/footer).
  chip?: "always" | "dark";
  // The lockup sits on the navy panel/rail → wordmark renders white.
  onDark?: boolean;
  // "lg" — hero lockup (seal over wordmark). "sm" — compact header lockup.
  size?: "sm" | "lg";
  layout?: "vertical" | "horizontal";
  // Seal-only treatment (no wordmark, no gap) — used by the sidebar's
  // collapsed rail where only the mark fits.
  markOnly?: boolean;
  className?: string;
}

// BrandMark: the Masar seal + wordmark as a single lockup. The seal is
// logo.png (the original square mark from design/) — never recolored or
// redrawn. The seal is always primary navy; when a `chip` is requested it
// sits on a white (bg-neutral-50) backing that is MASKED to the logo's own
// silhouette (mask-image of the same asset), so the backing hugs the seal
// shape instead of reading as a white box around the image. The wordmark is
// primary-navy on background surfaces and white on the navy panel/rail
// (`onDark`). Rendered where the brand appears in the auth area; promote to
// top-level shared/ when a second feature needs it (R7).
export function BrandMark({
  chip,
  onDark = false,
  size = "lg",
  layout = "vertical",
  markOnly = false,
  className,
}: BrandMarkProps) {
  return (
    <div
      className={cn(
        "flex items-center",
        layout === "vertical" ? "flex-col gap-2" : "gap-3",
        className,
      )}
    >
      <Image
        src="/logo.png"
        alt="Masar Logo"
        width={165}
        height={165}
        className={cn(
          "shrink-0",
          size === "lg" ? "size-14" : "size-9",
          chip === "always" && "bg-neutral-50",
          chip === "dark" && "dark:bg-neutral-50",
          chip &&
            "[mask-image:url(/logo.png)] [mask-position:center] [mask-size:contain] [-webkit-mask-image:url(/logo.png)] [-webkit-mask-position:center] [-webkit-mask-size:contain]",
        )}
      />
      {markOnly ? null : (
        <span
          className={cn(
            "font-sans font-semibold leading-none tracking-tight",
            size === "lg" ? "text-5xl" : "text-2xl",
            onDark ? "text-neutral-50" : "text-primary-text",
          )}
        >
          Mas<span className="text-secondary">ar</span>
        </span>
      )}
    </div>
  );
}
