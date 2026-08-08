import { getCookie } from "./cookies";
import type { Session, Role, CompanyStatus } from "@/types/auth";

// Reads the session straight from cookies for use in server components/actions.
// Mirrors what middleware.ts checks, but middleware can't import "server-only"
// code, so this is a separate, page-side read.
export async function getSession(): Promise<Session | null> {
  const token = await getCookie("jwt");
  const role = (await getCookie("role")) as Role | undefined;
  const companyStatus = (await getCookie("companyStatus")) as CompanyStatus | undefined;

  if (!token || !role) return null;

  return { token, role, companyStatus };
}
