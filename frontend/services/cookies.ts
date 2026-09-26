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
