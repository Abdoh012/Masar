import { NextRequest, NextResponse } from "next/server";
import {
  PUBLIC_ROUTES,
  AUTH_ROUTES_PREFIX,
  ROLE_HOME,
  COMPANY_PENDING_ROUTE,
} from "./config/routes";
import type { Role } from "./types/auth";
import {
  ACCESS_TOKEN_COOKIE,
  ACCESS_TOKEN_MAX_AGE,
  COMPANY_STATUS_COOKIE,
  CSRF_TOKEN_COOKIE,
  REFRESH_TOKEN_COOKIE,
  REFRESH_TOKEN_MAX_AGE,
  ROLE_COOKIE,
  SESSION_COOKIES,
  parseSetCookie,
  replaceCookiesInHeader,
} from "./shared/lib/authCookies";

const API_URL =
  process.env.NEXT_PUBLIC_API_URL ?? "http://localhost:8000/api/v1";

type RefreshedSession = {
  accessToken: string;
  refreshToken?: string;
  csrfToken?: string;
};

type RefreshOutcome =
  | { status: "success"; session: RefreshedSession }
  | { status: "rejected" }
  | { status: "unavailable" };

// Reads the JWT payload's `exp` without verifying the signature — only the
// backend holds the signing secret, and expiry is the only condition middleware
// needs to detect. Every request that follows is still verified by the backend,
// so an unparseable/garbage token is simply treated as "needs refreshing" and
// ends up rejected there if it really is invalid.
function isAccessTokenExpired(token: string): boolean {
  const payload = token.split(".")[1];
  if (!payload) return true;

  try {
    const base64 = payload.replace(/-/g, "+").replace(/_/g, "/");
    const padded = base64.padEnd(base64.length + ((4 - (base64.length % 4)) % 4), "=");
    const claims = JSON.parse(atob(padded)) as { exp?: unknown } | null;
    if (typeof claims?.exp !== "number") return true;
    return claims.exp * 1000 <= Date.now();
  } catch {
    return true;
  }
}

// Exchanges the httpOnly refresh_token cookie (plus its CSRF pair) for a fresh
// access token. Runs as a raw fetch so it can hand the cookies to the endpoint
// as request headers and cannot recurse into this middleware.
//
// The backend rotates on every call: the presented refresh token is revoked and
// only the replacement is returned in Set-Cookie. That is precisely why this
// must run here and not inside a Server Component render — middleware can both
// forward the fresh cookies to the render and persist them on the response,
// whereas `cookies().set()` is only permitted while a Server Action executes.
async function attemptRefresh(
  refreshToken: string,
  csrfToken: string,
): Promise<RefreshOutcome> {
  let res: Response;
  try {
    res = await fetch(`${API_URL}/auth/refresh`, {
      method: "POST",
      headers: {
        Cookie: `${REFRESH_TOKEN_COOKIE}=${refreshToken}; ${CSRF_TOKEN_COOKIE}=${csrfToken}`,
        "X-CSRF-Token": csrfToken,
      },
      cache: "no-store",
    });
  } catch {
    // Backend unreachable — not a rejection. Leave the session untouched so a
    // transient outage surfaces as the normal API error instead of a logout.
    return { status: "unavailable" };
  }

  if (!res.ok) return { status: "rejected" };

  const data = await res.json().catch(() => null);
  const accessToken =
    typeof data?.data?.token === "string" ? data.data.token : undefined;
  if (!accessToken) return { status: "rejected" };

  const rotated = parseSetCookie(res);
  return {
    status: "success",
    session: {
      accessToken,
      refreshToken: rotated.find((c) => c.name === REFRESH_TOKEN_COOKIE)?.value,
      csrfToken: rotated.find((c) => c.name === CSRF_TOKEN_COOKIE)?.value,
    },
  };
}

// Persists the rotated session on the outgoing response. Mirrors the attributes
// services/cookies.ts setCookie() writes (path "/", HttpOnly, SameSite=Lax) so
// the browser simply overwrites the existing cookies.
function persistSession(res: NextResponse, session: RefreshedSession): void {
  res.cookies.set(ACCESS_TOKEN_COOKIE, session.accessToken, {
    path: "/",
    httpOnly: true,
    sameSite: "lax",
    maxAge: ACCESS_TOKEN_MAX_AGE,
  });
  if (session.refreshToken) {
    res.cookies.set(REFRESH_TOKEN_COOKIE, session.refreshToken, {
      path: "/",
      httpOnly: true,
      sameSite: "lax",
      maxAge: REFRESH_TOKEN_MAX_AGE,
    });
  }
  if (session.csrfToken) {
    res.cookies.set(CSRF_TOKEN_COOKIE, session.csrfToken, {
      path: "/",
      httpOnly: true,
      sameSite: "lax",
      maxAge: REFRESH_TOKEN_MAX_AGE,
    });
  }
}

function clearSession(res: NextResponse): void {
  for (const name of SESSION_COOKIES) res.cookies.delete(name);
}

// Continues the request, handing the render a cookie header that already
// carries the freshly rotated session. Without this the RSC render would read
// the stale (expired) access token and the revoked refresh token for the rest
// of the request, and a second, doomed refresh would be attempted.
function continueRequest(
  request: NextRequest,
  session: RefreshedSession | null,
): NextResponse {
  if (!session) return NextResponse.next();

  const updates: Record<string, string> = {
    [ACCESS_TOKEN_COOKIE]: session.accessToken,
  };
  if (session.refreshToken) updates[REFRESH_TOKEN_COOKIE] = session.refreshToken;
  if (session.csrfToken) updates[CSRF_TOKEN_COOKIE] = session.csrfToken;

  const requestHeaders = new Headers(request.headers);
  requestHeaders.set(
    "cookie",
    replaceCookiesInHeader(request.headers.get("cookie"), updates),
  );

  const res = NextResponse.next({ request: { headers: requestHeaders } });
  persistSession(res, session);
  return res;
}

export async function middleware(request: NextRequest) {
  const { pathname } = request.nextUrl;
  const token = request.cookies.get(ACCESS_TOKEN_COOKIE)?.value;
  const role = request.cookies.get(ROLE_COOKIE)?.value as Role | undefined;
  const companyStatus = request.cookies.get(COMPANY_STATUS_COOKIE)?.value;

  const isPublic = PUBLIC_ROUTES.includes(pathname);
  const isAuthRoute = AUTH_ROUTES_PREFIX.some((p) => pathname.startsWith(p));
  const isPendingRoute = pathname === COMPANY_PENDING_ROUTE;

  // A session-expired landing (serverFetch redirects here after a 401 it could
  // not recover from). Cookie deletion isn't possible during an RSC render, so
  // force-clear the session here or the still-present token would bounce the
  // auth page straight back to the protected area — a redirect loop. This runs
  // before any refresh attempt: an explicitly dead session must not be revived.
  const isSessionExpired =
    request.nextUrl.searchParams.get("sessionExpired") === "1";
  if (isAuthRoute && isSessionExpired) {
    const res = NextResponse.next();
    clearSession(res);
    return res;
  }

  // Stale role cookie with no valid token — clear it, don't loop
  if (!token && role) {
    const res = NextResponse.redirect(
      new URL(isPublic || isAuthRoute ? pathname : "/sign-in", request.url),
    );
    res.cookies.delete(ROLE_COOKIE);
    res.cookies.delete(COMPANY_STATUS_COOKIE);
    return res;
  }

  // Not logged in
  if (!token) {
    // The pending-approval page is the company-registration landing: a fresh
    // company has no token by design (login is blocked until approval), so it
    // must render without a session.
    if (isPublic || isAuthRoute || isPendingRoute) return NextResponse.next();
    return NextResponse.redirect(new URL("/sign-in", request.url));
  }

  // Logged in. Refresh an expired access token up front so the render, and
  // every server-side read inside it, see a live token and the rotated refresh
  // token — instead of each 401ing and re-presenting an already-revoked token.
  let session: RefreshedSession | null = null;
  if (isAccessTokenExpired(token)) {
    const refreshToken = request.cookies.get(REFRESH_TOKEN_COOKIE)?.value;
    const csrfToken = request.cookies.get(CSRF_TOKEN_COOKIE)?.value;

    if (refreshToken && csrfToken) {
      const outcome = await attemptRefresh(refreshToken, csrfToken);

      if (outcome.status === "rejected") {
        // The refresh token itself is no longer usable (expired, revoked, or
        // already consumed by a concurrent refresh). Drop the whole session so
        // the browser stops believing the old token still works, instead of
        // re-presenting it on every later request.
        const res = NextResponse.redirect(
          new URL(
            `/sign-in?sessionExpired=1&error=${encodeURIComponent("Your session has expired. Please sign in again.")}`,
            request.url,
          ),
        );
        clearSession(res);
        return res;
      }

      if (outcome.status === "success") session = outcome.session;
    }
    // No refresh/CSRF pair, or the backend was unreachable: keep the session as
    // it is. The normal flow then surfaces the failure — a 401 becomes the
    // session-expired redirect (which clears the cookies on the way in), and an
    // outage becomes the usual "Unable to reach the server" error.
  }

  const redirectWithSession = (url: URL): NextResponse => {
    const res = NextResponse.redirect(url);
    if (session) persistSession(res, session);
    return res;
  };

  // keep off auth forms, public pages stay accessible to everyone
  if (isAuthRoute) {
    return redirectWithSession(
      new URL(role ? ROLE_HOME[role] : "/dashboard", request.url),
    );
  }

  if (isPublic) return continueRequest(request, session);

  // Role gating
  if (role === "admin" && !pathname.startsWith("/admin")) {
    return redirectWithSession(new URL(ROLE_HOME.admin, request.url));
  }

  if (role === "company") {
    if (!pathname.startsWith("/company")) {
      return redirectWithSession(new URL(ROLE_HOME.company, request.url));
    }
    // Approval status never gates company-area access — it only controls
    // features/actions that require approval. The one status rule kept here
    // is steering approved companies off the pending-approval waiting page
    // (the registration landing for token-less fresh companies).
    if (companyStatus !== "pending" && pathname === COMPANY_PENDING_ROUTE) {
      return redirectWithSession(new URL(ROLE_HOME.company, request.url));
    }
  }

  if (
    role === "student" &&
    (pathname.startsWith("/company") || pathname.startsWith("/admin"))
  ) {
    return redirectWithSession(new URL(ROLE_HOME.student, request.url));
  }

  return continueRequest(request, session);
}

export const config = {
  matcher: [
    "/((?!api|_next/static|_next/image|favicon.ico|.*\\.(?:svg|png|jpg|jpeg|gif|webp)$).*)",
  ],
};
