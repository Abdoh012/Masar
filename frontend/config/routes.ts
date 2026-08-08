import type { Role } from "@/types/auth";

export const PUBLIC_ROUTES = ["/", "/about", "/support", "/privacy", "/terms"];

export const AUTH_ROUTES_PREFIX = [
  "/sign-in",
  "/sign-up",
  "/forgot-password",
  "/reset-password",
];

// Where each role lands after sign-in, or when they hit a route they can't access.
export const ROLE_HOME: Record<Role, string> = {
  student: "/dashboard",
  company: "/company/dashboard",
  admin: "/admin/companies",
};

export const COMPANY_PENDING_ROUTE = "/company/pending-approval";
