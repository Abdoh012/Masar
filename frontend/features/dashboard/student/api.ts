// Server-side read layer for the student dashboard (role-level api, mirroring
// features/applications/student/api.ts). One endpoint, one call per render.
import { serverFetch } from "@/services/api";
import type { TryCatchResponse } from "@/types/server-action";

/** Fetches the whole student dashboard in a single read
 *  (GET /students/dashboard) and returns the TryCatchResponse envelope.
 *
 *  cache: "no-store" — every section is scoped to the authenticated student and
 *  must be fresh after any application/training/certificate change. Non-throwing,
 *  so the container decides how to surface a failure. */
export async function fetchStudentDashboard(): Promise<TryCatchResponse> {
  return serverFetch({
    url: "students/dashboard",
    cache: "no-store",
  });
}
