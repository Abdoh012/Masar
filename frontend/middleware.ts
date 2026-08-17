import { NextRequest, NextResponse } from "next/server";
import {
  PUBLIC_ROUTES,
  AUTH_ROUTES_PREFIX,
  ROLE_HOME,
  COMPANY_PENDING_ROUTE,
} from "./config/routes";
import type { Role } from "./types/auth";

export function middleware(request: NextRequest) {
  const { pathname } = request.nextUrl;
  const token = request.cookies.get("masarJwt")?.value;
  const role = request.cookies.get("masarRole")?.value as Role | undefined;
  const companyStatus = request.cookies.get("companyStatus")?.value;

  const isPublic = PUBLIC_ROUTES.includes(pathname);
  const isAuthRoute = AUTH_ROUTES_PREFIX.some((p) => pathname.startsWith(p));

  // Stale role cookie with no valid token — clear it, don't loop
  if (!token && role) {
    const res = NextResponse.redirect(
      new URL(isPublic || isAuthRoute ? pathname : "/sign-in", request.url),
    );
    res.cookies.delete("masarRole");
    res.cookies.delete("companyStatus");
    return res;
  }

  // Not logged in
  if (!token) {
    if (isPublic || isAuthRoute) return NextResponse.next();
    return NextResponse.redirect(new URL("/sign-in", request.url));
  }

  // Logged in: keep off auth forms, public pages stay accessible to everyone
  if (isAuthRoute) {
    return NextResponse.redirect(
      new URL(role ? ROLE_HOME[role] : "/dashboard", request.url),
    );
  }

  if (isPublic) return NextResponse.next();

  // Role gating
  if (role === "admin" && !pathname.startsWith("/admin")) {
    return NextResponse.redirect(new URL(ROLE_HOME.admin, request.url));
  }

  if (role === "company") {
    if (!pathname.startsWith("/company")) {
      return NextResponse.redirect(new URL(ROLE_HOME.company, request.url));
    }
    // Pending companies are locked to the waiting page until Admin approves
    if (companyStatus !== "approved" && pathname !== COMPANY_PENDING_ROUTE) {
      return NextResponse.redirect(new URL(COMPANY_PENDING_ROUTE, request.url));
    }
  }

  if (
    role === "student" &&
    (pathname.startsWith("/company") || pathname.startsWith("/admin"))
  ) {
    return NextResponse.redirect(new URL(ROLE_HOME.student, request.url));
  }

  return NextResponse.next();
}

export const config = {
  matcher: [
    "/((?!api|_next/static|_next/image|favicon.ico|.*\\.(?:svg|png|jpg|jpeg|gif|webp)$).*)",
  ],
};
