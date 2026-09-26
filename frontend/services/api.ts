"use server";

import type { TryCatchRequest, TryCatchResponse } from "@/types/server-action";
import { redirect } from "next/navigation";
import { isRedirectError } from "next/dist/client/components/redirect-error";
import {
  ACCESS_TOKEN_COOKIE,
  SESSION_COOKIES,
  parseSetCookie,
} from "@/shared/lib/authCookies";
import { deleteCookie, getCookie } from "./cookies";

const API_URL =
  process.env.NEXT_PUBLIC_API_URL ?? "http://localhost:8000/api/v1";

// Clears the whole session. Each delete is guarded individually: Next.js only
// permits cookie mutation inside Server Actions and Route Handlers, so during
// a Server Component render deleteCookie() throws — swallowing that keeps the
// failure distinct from a real API/network failure. The cookies are genuinely
// removed by the middleware `sessionExpired=1` branch this redirects into,
// which is always able to write to the response.
async function clearAuthCookies(): Promise<void> {
  for (const name of SESSION_COOKIES) {
    try {
      await deleteCookie(name);
    } catch {
      // Mutation isn't permitted in this context; the redirect below routes
      // through middleware, which clears the session for real.
    }
  }
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

    // Refreshing an expired access token is middleware's job: only there can the
    // backend's refresh-token rotation be persisted, because cookies are
    // immutable during a Server Component render. By the time a request reaches
    // this layer its cookies already carry a live token, so no token override or
    // second attempt is needed here.
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

    const res = await fetch(`${API_URL}/${url}`, fetchOptions);

    if (!res.ok) {
      // Error bodies aren't guaranteed JSON (e.g. a gateway 502/504 may return
      // an HTML page); parse defensively so a non-JSON body still yields a
      // proper error message rather than a network failure.
      const data = await res.json().catch(() => null);

      // A 401 on a request that carried a Bearer token means the session the
      // browser is holding is genuinely rejected — revoked, deactivated, or a
      // rotation that could not be completed. Drop every cookie and bounce to
      // sign-in with the expired flag so the user is actually logged out rather
      // than stranded. A 401 on a request sent WITHOUT a token is the endpoint
      // refusing the call itself (e.g. acting on a record the caller doesn't
      // own) and must not log anyone out. Auth endpoints opt out so a
      // wrong-password 401 on login never misfires into a redirect.
      // redirect() throws NEXT_REDIRECT, which the outer catch re-throws.
      if (res.status === 401 && token && !isAuthEndpoint) {
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
