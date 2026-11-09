import "server-only";
import { cookies } from "next/headers";
import {
  ACCESS_TOKEN_COOKIE,
  ACCESS_TOKEN_MAX_AGE,
  COMPANY_STATUS_COOKIE,
  CSRF_TOKEN_COOKIE,
  REFRESH_TOKEN_COOKIE,
  REFRESH_TOKEN_MAX_AGE,
  ROLE_COOKIE,
  SESSION_COOKIES,
} from "@/shared/lib/authCookies";

// Names/lifetimes live in shared/lib/authCookies so middleware (Edge runtime,
// can't import "server-only") and these server-only helpers share one source.
export {
  ACCESS_TOKEN_COOKIE,
  ACCESS_TOKEN_MAX_AGE,
  COMPANY_STATUS_COOKIE,
  CSRF_TOKEN_COOKIE,
  REFRESH_TOKEN_COOKIE,
  REFRESH_TOKEN_MAX_AGE,
  ROLE_COOKIE,
};

export async function getCookie(name: string) {
  const store = await cookies();
  return store.get(name)?.value;
}

export async function setCookie(
  name: string,
  value: string,
  options: Partial<{ maxAge: number; path: string; httpOnly: boolean }> = {},
) {
  const store = await cookies();
  store.set(name, value, {
    path: "/",
    httpOnly: true,
    sameSite: "lax",
    ...options,
  });
}

export async function deleteCookie(name: string) {
  const store = await cookies();
  store.delete(name);
}

// Drops the whole session (access/role/company-status plus the backend-minted
// refresh pair). Only callable where Next permits cookie mutation — a Server
// Action or a Route Handler — so callers are responsible for navigating the user
// somewhere afterwards.
export async function clearSessionCookies(): Promise<void> {
  for (const name of SESSION_COOKIES) {
    await deleteCookie(name);
  }
}
