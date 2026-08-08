import Link from "next/link";

interface PageNotFoundProps {
  // What couldn't be found (e.g. "Reset link") — shown in the headline.
  label: string;
  backHref: string;
  backLabel: string;
}

// PageNotFound: centered "invalid or expired" state for token-based routes
// (reset-password, and later invite links). Renders inside whatever layout
// owns the route, so it relies on semantic tokens rather than absolute
// positioning.
export function PageNotFound({ label, backHref, backLabel }: PageNotFoundProps) {
  const lowerLabel = label.toLowerCase();
  return (
    <div className="mx-auto flex min-h-[55vh] max-w-sm flex-col items-center justify-center gap-3 px-6 text-center">
      <span className="font-mono text-xs font-semibold uppercase tracking-[0.2em] text-secondary-500">
        404
      </span>
      <h2 className="font-sans text-xl font-semibold text-primary-text">
        Invalid or expired {lowerLabel}
      </h2>
      <p className="text-sm leading-relaxed text-muted-foreground">
        This {lowerLabel} may have already been used, or it might be out of date. Request a
        new one to continue.
      </p>
      <Link
        href={backHref}
        className="mt-2 text-sm font-medium text-secondary-text underline-offset-4 hover:underline"
      >
        {backLabel}
      </Link>
    </div>
  );
}
