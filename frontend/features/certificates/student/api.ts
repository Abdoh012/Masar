// Server-side read layer for the student certificates page (role-level api,
// mirroring features/applications/student/api.ts). Routes through serverFetch
// with cache: "no-store" — certificate data is per-student and must be fresh
// after a request or any staff-side change. Consumed by the async server
// CertificatePageContent orchestrator during render; never fetched client-side.
import { serverFetch } from "@/services/api";
import type { TryCatchResponse } from "@/types/server-action";

/** Fetches the certificate statistics (GET /certificates/statistics):
 *  `{ total, issued, active, valid, revoked, pending }`. The backend reports
 *  its eligible/requestable count under the `valid` key — that is the source
 *  for the "Eligible to request" stat card (see normalize.ts). Non-throwing
 *  TryCatchResponse; the orchestrator throws on !res.success so the route-level
 *  error boundary renders. */
export async function fetchCertificateStats(): Promise<TryCatchResponse> {
  return serverFetch({
    url: "certificates/statistics",
    cache: "no-store",
  });
}

/** Fetches the eligible-to-request trainings feed (GET /certificates/eligible):
 *  the requestable trainings the page lists under the "Eligible to request"
 *  heading. The eligible stat CARD count, however, comes from
 *  statistics.valid, not from the length of this feed. Non-throwing
 *  TryCatchResponse; the orchestrator throws on failure. */
export async function fetchEligibleFeed(): Promise<TryCatchResponse> {
  return serverFetch({
    url: "certificates/eligible",
    cache: "no-store",
  });
}

/** Fetches the authenticated student's certificate records
 *  (GET /certificates?limit=100 — the controller default limit is 20; 100
 *  covers a student's full records list without pagination UI). Non-throwing
 *  TryCatchResponse; the orchestrator throws on failure. */
export async function fetchStudentCertificates(): Promise<TryCatchResponse> {
  return serverFetch({
    url: "certificates?limit=100",
    cache: "no-store",
  });
}

/** Fetches the authenticated student's pending (requested, still awaiting
 *  confirmation) certificates — GET /certificates/pending. Non-throwing
 *  TryCatchResponse; the orchestrator throws on failure. */
export async function fetchPendingCertificates(): Promise<TryCatchResponse> {
  return serverFetch({
    url: "certificates/pending",
    cache: "no-store",
  });
}

/** Fetches the authenticated student's issued certificates —
 *  GET /certificates/issued. Non-throwing TryCatchResponse; the orchestrator
 *  throws on failure. */
export async function fetchIssuedCertificates(): Promise<TryCatchResponse> {
  return serverFetch({
    url: "certificates/issued",
    cache: "no-store",
  });
}

/** Fetches the authenticated student's revoked certificates —
 *  GET /certificates/revoked. Non-throwing TryCatchResponse; the orchestrator
 *  throws on failure. */
export async function fetchRevokedCertificates(): Promise<TryCatchResponse> {
  return serverFetch({
    url: "certificates/revoked",
    cache: "no-store",
  });
}