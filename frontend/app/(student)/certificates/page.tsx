import type { Metadata } from "next";
import { Suspense } from "react";

import {
  CertificatePageContent,
  CertificatesPageSkeleton,
} from "@/features/certificates";

export const metadata: Metadata = {
  title: "My Certificates",
};

// force-dynamic: the server content fetches authenticated statistics during
// render — without opting out, `next build` would prerender the fetch without
// the user's cookies and fail. (The applications page doesn't need this: it
// reads searchParams, which makes it dynamic automatically.)
export const dynamic = "force-dynamic";

// Thin composition point: single section behind one Suspense boundary with the
// page-shaped skeleton as its fallback (architecture §3, FR-028). The page
// itself owns all structure and copy; this shell imports only from the feature
// index.
export default function Page() {
  return (
    <main className="mx-auto flex w-full max-w-6xl flex-col gap-6 p-4 sm:p-6 lg:p-8">
      <Suspense fallback={<CertificatesPageSkeleton />}>
        <CertificatePageContent />
      </Suspense>
    </main>
  );
}
