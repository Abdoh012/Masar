import type { Role } from "@/types/auth";

export const PUBLIC_ROUTES = ["/", "/about", "/support", "/privacy", "/terms"];

export const AUTH_ROUTES_PREFIX = [
  "/sign-in",
  "/sign-up",
  "/profile-information",
  "/forgot-password",
  "/verify-otp",
  "/reset-password",
  "/password-updated",
];

// Where each role lands after sign-in, or when they hit a route they can't access.
export const ROLE_HOME: Record<Role, string> = {
  student: "/dashboard",
  company: "/company/dashboard",
  admin: "/admin/companies",
};

export const COMPANY_PENDING_ROUTE = "/company/pending-approval";

// Transient landing shown after a successful password reset; auto-redirects
// to /sign-in after a few seconds.
export const PASSWORD_UPDATED_ROUTE = "/password-updated";
