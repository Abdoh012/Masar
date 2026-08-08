import Link from "next/link";

export default function NotFound() {
  return (
    <div className="flex min-h-screen flex-col items-center justify-center gap-3 px-6 text-center">
      <h2 className="font-sans text-lg font-semibold text-navy">
        Page not found
      </h2>
      <p className="max-w-sm text-sm text-mid">
        The page you&apos;re looking for doesn&apos;t exist or may have moved.
      </p>
      <Link href="/" className="text-sm font-medium text-gold">
        Back home
      </Link>
    </div>
  );
}
