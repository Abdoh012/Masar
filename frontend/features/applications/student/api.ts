// Server-side read layer for the student My Applications page (role-level api,
// mirroring features/listings/student/api.ts). Routes through serverFetch with
// cache: "no-store" — application data is per-student and must be fresh after a
// withdraw or any staff-side change.
import { serverFetch } from "@/services/api";
import type { TryCatchResponse } from "@/types/server-action";
import { TabValue } from "@/features/applications/student/types";

// The five My Applications tabs map 1:1 to list endpoints; /all returns every
// status mixed. Everything else is driven by ?page= and ?limit= (max 100).
// The page calls this once per render with the ACTIVE tab only (no cross-tab
// fetching), and reads limit=100 so a single request covers a student's full
// list without pagination UI (universities cap submittable applications well
// below that).
const TAB_ENDPOINTS: Record<TabValue, string> = {
  all: "all",
  applied: "applied",
  accepted: "accepted",
  rejected: "rejected",
  withdrawn: "withdrawn",
};

export const APPLICATIONS_PAGE_LIMIT = 100;

/** Fetches one tab's application cards. Non-throwing TryCatchResponse; the
 *  container throws on !res.success so the route-level error boundary renders. */
export async function fetchApplicationsTab(
  tab: TabValue,
): Promise<TryCatchResponse> {
  return serverFetch({
    url: `applications/${TAB_ENDPOINTS[tab]}?page=1&limit=${APPLICATIONS_PAGE_LIMIT}`,
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