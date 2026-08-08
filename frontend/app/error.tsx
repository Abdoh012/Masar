"use client";

export default function Error({
  error,
  reset,
}: {
  error: Error & { digest?: string };
  reset: () => void;
}) {
  return (
    <div className="flex min-h-screen flex-col items-center justify-center gap-3 px-6 text-center">
      <h2 className="font-sans text-lg font-semibold text-navy">
        Something went wrong
      </h2>
      <p className="max-w-sm text-sm text-mid">{error.message}</p>
      <button
        onClick={() => reset()}
        className="rounded-md bg-navy px-4 py-2 text-sm font-medium text-white"
      >
        Try again
      </button>
    </div>
  );
}
