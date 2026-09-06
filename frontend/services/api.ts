"use server";

import type { TryCatchRequest, TryCatchResponse } from "@/types/server-action";
import { redirect } from "next/navigation";
import { isRedirectError } from "next/dist/client/components/redirect-error";
import {
  ACCESS_TOKEN_COOKIE,
  ACCESS_TOKEN_MAX_AGE,
  COMPANY_STATUS_COOKIE,
  CSRF_TOKEN_COOKIE,
  REFRESH_TOKEN_COOKIE,
  REFRESH_TOKEN_MAX_AGE,
  ROLE_COOKIE,
} from "./cookies";
import { deleteCookie, getCookie, setCookie } from "./cookies";

const API_URL =
  process.env.NEXT_PUBLIC_API_URL ?? "http://localhost:8000/api/v1";

type BackendCookie = { name: string; value: string };

// undici drops Set-Cookie from the headers map; getSetCookie() (Node 20+)
// exposes the raw headers a browser would have received, so the server-side
// flow can forward cookies the backend mints at runtime.
function parseSetCookie(res: Response): BackendCookie[] {
  const raw =
    typeof res.headers.getSetCookie === "function"
      ? res.headers.getSetCookie()
      : [res.headers.get("set-cookie")].filter((h): h is string => Boolean(h));
  const result: BackendCookie[] = [];
  for (const header of raw) {
    const pair = header.split(";", 1)[0];
    const eq = pair.indexOf("=");
    if (eq <= 0) continue;
    result.push({
      name: pair.slice(0, eq).trim(),
      value: pair.slice(eq + 1).trim(),
    });
  }
  return result;
}

// Clears the whole session. Each delete is guarded individually: Next.js only
// permits cookie mutation inside Server Actions and Route Handlers, so during
// a Server Component render deleteCookie() throws — swallowing that keeps the
// failure distinct from a real API/network failure.
async function clearAuthCookies(): Promise<void> {
  for (const name of [
    ACCESS_TOKEN_COOKIE,
    ROLE_COOKIE,
    COMPANY_STATUS_COOKIE,
    REFRESH_TOKEN_COOKIE,
    CSRF_TOKEN_COOKIE,
  ]) {
    try {
      await deleteCookie(name);
    } catch {
      // Mutation isn't permitted in this context; the caller already falls
      // back to the existing unauthenticated behavior.
    }
  }
}

// Result of a single refresh attempt. The fresh access token (plus any rotated
// refresh/csrf values) is RETURNED rather than written to cookies here: each
// concurrent serverFetch has an isolated request-scoped cookie store, so every
// caller applies the result to its own store before retrying.
type RefreshResult =
  | {
      status: "success";
      accessToken: string;
      refreshToken?: string;
      csrfToken?: string;
    }
  | { status: "failed" };

// Exchanges the httpOnly refresh_token cookie (plus its CSRF pair) for a fresh
// access token. Runs as a raw fetch — never through serverFetch — so it can
// hand the refresh/CSRF cookie values to the endpoint as request headers and
// cannot recurse. Runs without an access token too: a request whose access
// cookie has been dropped but whose refresh cookie survives can be restored.
export async function refreshAccessToken(): Promise<RefreshResult> {
  const refreshToken = await getCookie(REFRESH_TOKEN_COOKIE);
  const csrfToken = await getCookie(CSRF_TOKEN_COOKIE);

  if (!refreshToken || !csrfToken) return { status: "failed" };

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
    return { status: "failed" };
  }

  if (!res.ok) return { status: "failed" };

  const data = await res.json().catch(() => null);
  const accessToken =
    typeof data?.data?.token === "string" ? data.data.token : undefined;
  if (!accessToken) return { status: "failed" };

  const rotated = parseSetCookie(res);
  return {
    status: "success",
    accessToken,
    refreshToken: rotated.find((c) => c.name === REFRESH_TOKEN_COOKIE)?.value,
    csrfToken: rotated.find((c) => c.name === CSRF_TOKEN_COOKIE)?.value,
  };
}

// Writes a successful refresh result into THIS request's cookie store. Every
// caller runs this after awaiting the shared single-flight refresh so waiters
// whose own store didn't perform the exchange still retry with a fresh token.
// Cookie writes are read-only during a Server Component render, so failures are
// swallowed — the caller still retries with the returned token via the
// override, and rotation is deferred to the next mutable request.
async function applyRefreshResult(result: RefreshResult): Promise<void> {
  if (result.status !== "success") return;
  try {
    await setCookie(ACCESS_TOKEN_COOKIE, result.accessToken, {
      maxAge: ACCESS_TOKEN_MAX_AGE,
    });
    if (result.refreshToken) {
      await setCookie(REFRESH_TOKEN_COOKIE, result.refreshToken, {
        maxAge: REFRESH_TOKEN_MAX_AGE,
      });
    }
    if (result.csrfToken) {
      await setCookie(CSRF_TOKEN_COOKIE, result.csrfToken, {
        maxAge: REFRESH_TOKEN_MAX_AGE,
      });
    }
  } catch {
    // Read-only cookie store (RSC render): rotation deferred; the retry still
    // carries the fresh token via doFetch's override.
  }
}

// Single-flight guard, keyed by the session's refresh-token cookie value:
// concurrent serverFetch calls from the SAME session share one refresh
// round-trip instead of each firing their own. Requests from DIFFERENT
// sessions never share a promise — a process-global single-flight would hand
// one user's freshly-minted access token to a concurrent request from another
// user waiting on the same promise, leaking that user's data.
const refreshPromises = new Map<string, Promise<RefreshResult>>();

const NO_REFRESH_KEY = "__no-refresh__";

async function refreshOnce(): Promise<RefreshResult> {
  const sessionKey =
    (await getCookie(REFRESH_TOKEN_COOKIE)) ?? NO_REFRESH_KEY;

  const inflight = refreshPromises.get(sessionKey);
  if (inflight) return inflight;

  const promise = refreshAccessToken().finally(() => {
    refreshPromises.delete(sessionKey);
  });
  refreshPromises.set(sessionKey, promise);
  return promise;
}

export async function serverFetch({
  url,
  method = "GET",
  body,
  cache = "default",
  revalidate,
}: TryCatchRequest): Promise<TryCatchResponse> {
  try {
    const isFormData = body instanceof FormData;
    const isAuthEndpoint = url.startsWith("auth/");
    const hadToken = Boolean(await getCookie(ACCESS_TOKEN_COOKIE));
    const hasRefreshToken = Boolean(await getCookie(REFRESH_TOKEN_COOKIE));
    // A request is "session-relevant" when the browser proves a session exists:
    // an access token is present, or a refresh token that could restore one.
    // Auth endpoints (login/register/forgot/otp/reset/logout) opt out of both
    // auto-refresh and the session-expired redirect, so a wrong-password 401
    // on login never misfires into a redirect.
    const sessionRelevant = hadToken || hasRefreshToken;

    // Builds the request per attempt. The override lets a retry carry the
    // freshly minted token even when the cookie store is read-only (RSC
    // render), where the stale cookie cannot be updated.
    const doFetch = async (accessTokenOverride?: string): Promise<Response> => {
      const token =
        accessTokenOverride ?? (await getCookie(ACCESS_TOKEN_COOKIE));
      const headers: Record<string, string> = {};
      if (!isFormData) headers["Content-Type"] = "application/json";
      if (token) headers["Authorization"] = `Bearer ${token}`;

      const fetchOptions = {
        method,
        headers,
        body: isFormData ? body : body ? JSON.stringify(body) : undefined,
        cache,
      } as RequestInit & { next?: { revalidate: number } };

      if (cache !== "no-store" && revalidate !== undefined) {
        fetchOptions.next = { revalidate };
      }

      return fetch(`${API_URL}/${url}`, fetchOptions);
    };

    let res = await doFetch();

    // Expired (or missing) access token on a session-tied request: refresh
    // once, then retry the original request exactly once. The shared result is
    // applied to this caller's own cookie store first, so concurrent callers
    // don't retry with the stale expired token and get logged out.
    if (res.status === 401 && sessionRelevant && !isAuthEndpoint) {
      const refreshed = await refreshOnce();
      if (refreshed.status === "success") {
        await applyRefreshResult(refreshed);
        res = await doFetch(refreshed.accessToken);
      }
    }

    if (!res.ok) {
      // Error bodies aren't guaranteed JSON (e.g. a gateway 502/504 may return
      // an HTML page); parse defensively so a non-JSON body still yields a
      // proper error message rather than a network failure.
      const data = await res.json().catch(() => null);

      // A 401 that refresh couldn't fix (refresh failed, or the retried
      // request was still rejected) means the session is no longer usable —
      // drop every cookie and bounce to sign-in with the expired flag so the
      // user is actually logged out, not left stranded on the page.
      // redirect() throws NEXT_REDIRECT, which the outer catch re-throws.
      if (res.status === 401 && sessionRelevant && !isAuthEndpoint) {
        await clearAuthCookies();
        const message =
          data?.message || "Your session has expired. Please sign in again.";
        redirect(`/sign-in?sessionExpired=1&error=${encodeURIComponent(message)}`);
      }

      return {
        success: false,
        error: data?.message || "Invalid data, please try again later",
        errors: data?.errors || undefined,
        userData: body,
        status: res.status,
      };
    }

    const resData = await res.json();
    const responseCookies = parseSetCookie(res);

    return {
      success: true,
      data: resData.data,
      message: resData.message,
      ...(responseCookies.length > 0 ? { cookies: responseCookies } : {}),
    };
  } catch (error) {
    if (isRedirectError(error)) throw error;
    return {
      success: false,
      error: "Unable to reach the server, please try again later",
    };
  }
}