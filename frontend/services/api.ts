"use server";

import type { TryCatchRequest, TryCatchResponse } from "@/types/server-action";
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

// Exchanges the httpOnly refresh_token cookie (plus its CSRF pair) for a fresh
// access token. Runs as a raw fetch — never through serverFetch — so it can
// hand the refresh/CSRF cookie values to the endpoint as request headers and
// cannot recurse. Success persists the new access token and the rotated
// refresh/CSRF cookies; failure drops the session cookies entirely.
export async function refreshAccessToken(): Promise<boolean> {
  const refreshToken = await getCookie(REFRESH_TOKEN_COOKIE);
  const csrfToken = await getCookie(CSRF_TOKEN_COOKIE);

  if (!refreshToken || !csrfToken) {
    await clearAuthCookies();
    return false;
  }

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
    await clearAuthCookies();
    return false;
  }

  if (!res.ok) {
    await clearAuthCookies();
    return false;
  }

  // Applying Set-Cookie can fail during a Server Component render (cookies are
  // read-only outside Server Actions / Route Handlers). That failure must not
  // leak into serverFetch's outer catch and read as a network error — report
  // the refresh as not applied, so the caller surfaces the underlying API
  // error instead of a misleading "Unable to reach the server".
  let accessToken: string | undefined;
  try {
    const data = await res.json();
    accessToken = typeof data.data?.token === "string" ? data.data.token : undefined;

    if (accessToken) {
      await setCookie(ACCESS_TOKEN_COOKIE, accessToken, {
        maxAge: ACCESS_TOKEN_MAX_AGE,
      });
    }

    const rotated = parseSetCookie(res);
    const newRefresh = rotated.find((c) => c.name === REFRESH_TOKEN_COOKIE)?.value;
    if (newRefresh) {
      await setCookie(REFRESH_TOKEN_COOKIE, newRefresh, {
        maxAge: REFRESH_TOKEN_MAX_AGE,
      });
    }
    const newCsrf = rotated.find((c) => c.name === CSRF_TOKEN_COOKIE)?.value;
    if (newCsrf) {
      await setCookie(CSRF_TOKEN_COOKIE, newCsrf, {
        maxAge: REFRESH_TOKEN_MAX_AGE,
      });
    }
  } catch {
    return false;
  }

  return Boolean(accessToken);
}

// Single-flight guard: concurrent serverFetch calls hitting an expired token
// share one refresh round-trip instead of each firing their own.
let refreshPromise: Promise<boolean> | null = null;

async function refreshOnce(): Promise<boolean> {
  if (!refreshPromise) {
    refreshPromise = refreshAccessToken().finally(() => {
      refreshPromise = null;
    });
  }
  return refreshPromise;
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
    const hadToken = Boolean(await getCookie(ACCESS_TOKEN_COOKIE));

    // Builds the request per attempt so a retry after a successful refresh
    // naturally picks up the fresh access token from the cookie store.
    const doFetch = async (): Promise<Response> => {
      const token = await getCookie(ACCESS_TOKEN_COOKIE);
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

    // Expired access token on an authenticated request: refresh once, then
    // retry the original request exactly once. Public requests (no token)
    // never trigger a refresh.
    if (res.status === 401 && hadToken) {
      const refreshed = await refreshOnce();
      if (refreshed) {
        res = await doFetch();
      }
    }

    if (!res.ok) {
      // A 401 that refresh couldn't fix (refresh failed, or the retried
      // request was still rejected) means the stored access token is no
      // longer usable — drop the session so the app returns to its
      // unauthenticated behavior instead of retrying a stale cookie forever.
      if (res.status === 401 && hadToken) {
        await clearAuthCookies();
      }

      // Error bodies aren't guaranteed JSON (e.g. a gateway 502/504 may return
      // an HTML page); parse defensively so a non-JSON body still yields a
      // proper error message rather than a network failure.
      const data = await res.json().catch(() => null);
      return {
        success: false,
        error: data?.message || "Invalid data, please try again later",
        errors: data?.errors || undefined,
        userData: body,
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
  } catch {
    return {
      success: false,
      error: "Unable to reach the server, please try again later",
    };
  }
}