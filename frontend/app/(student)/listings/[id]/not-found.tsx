import Link from "next/link";

export default function NotFound() {
  return (
    <div className="flex min-h-[40vh] flex-col items-center justify-center gap-3 px-6 text-center">
      <h2 className="font-sans text-base font-semibold text-navy">
        Training Listing not found
      </h2>
      <p className="max-w-sm text-sm text-mid">
        This training listing doesn&apos;t exist or may have been removed.
      </p>
      <Link href="/" className="text-sm font-medium text-gold">
        Back home
      </Link>
    </div>
  );
}
