// Server-side read layer for the student My Applications page (role-level api,
// mirroring features/listings/student/api.ts). Routes through serverFetch with
// cache: "no-store" — application data is per-student and must be fresh after a
// withdraw or any staff-side change.
import { serverFetch } from "@/services/api";
import type { TryCatchResponse } from "@/types/server-action";
import { TabValue } from "@/features/applications/student/types";

// The five status tabs map 1:1 to list endpoints; /all returns every status
// mixed. Each endpoint pages server-side via ?page= + ?limit= (limit clamped
// to 100 backend-side) and returns the { items, pagination } envelope — the
// backend computes offset from page, never the client. The page calls once per
// render with the ACTIVE status tab only (no cross-tab fetching) and passes
// the current ?page= through, so every tab pages at APPLICATIONS_PAGE_LIMIT.
// The ended tab is deliberately NOT here — it maps to /applications/
// certificates (fetchEndedApplications), a different dataset.
export type ApplicationStatusTabValue = Exclude<TabValue, "ended">;

const TAB_ENDPOINTS: Record<ApplicationStatusTabValue, string> = {
  all: "all",
  applied: "applied",
  accepted: "accepted",
  rejected: "rejected",
  withdrawn: "withdrawn",
};

// Pagination envelope shape (matches the backend response and the shared
// Pagination component's PaginationInfo). Mirrors features/listings/student/
// api.ts exactly so the applications tabs page exactly like the Trainings tab.
export interface Pagination {
  current_page: number;
  per_page: number;
  total: number;
  total_pages: number;
  has_next_page: boolean;
  has_previous_page: boolean;
}

// Cards per page across the Applications page, same as the Trainings page's
// BROWSE_PAGE_LIMIT.
export const APPLICATIONS_PAGE_LIMIT = 20;

/** Fetches one status tab's page of application cards. Non-throwing
 *  TryCatchResponse; the container throws on !res.success so the route-level
 *  error boundary renders. page is the 1-based page carried by ?page=. */
export async function fetchApplicationsTab(
  tab: ApplicationStatusTabValue,
  page: number,
): Promise<TryCatchResponse> {
  return serverFetch({
    url: `applications/${TAB_ENDPOINTS[tab]}?page=${page}&limit=${APPLICATIONS_PAGE_LIMIT}`,
    cache: "no-store",
  });
}

/** Fetches the ended-applications dataset (GET /applications/certificates) —
 *  the student's completed trainings with an issued certificate. The backend
 *  delegates this to the same issued handler as GET /certificates/issued, so
 *  the response is a bare array of issued certificate DTOs (no pagination
 *  envelope) — normalized by normalizeEndedApplications. Non-throwing
 *  TryCatchResponse; the ended container throws on failure. */
export async function fetchEndedApplications(): Promise<TryCatchResponse> {
  return serverFetch({
    url: "applications/certificates",
    cache: "no-store",
  });
}

/** Fetches an accepted-paid application's payment row (GET
 *  /applications/{id}/payment). The card DTO's payment_status stays "pending"
 *  whether or not a reference was already reported, so the `data.submitted`
 *  flag here is the server-side source for the "Payment reported" state. Read
 *  from the server ReportPaymentZone during render — no client fetch, and the
 *  success state falls out of revalidatePath + Next's automatic route refresh
 *  after reportPayment, not out of client state. */
export async function fetchPaymentStatus(
  applicationId: number,
): Promise<TryCatchResponse> {
  return serverFetch({
    url: `applications/${applicationId}/payment`,
    cache: "no-store",
  });
}