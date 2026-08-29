import "server-only";
import { cookies } from "next/headers";

export const ACCESS_TOKEN_COOKIE = "masarJwt";
export const ROLE_COOKIE = "masarRole";
export const COMPANY_STATUS_COOKIE = "companyStatus";
export const REFRESH_TOKEN_COOKIE = "refresh_token";
export const CSRF_TOKEN_COOKIE = "csrf_token";

export const ACCESS_TOKEN_MAX_AGE = 60 * 60 * 24 * 90;
export const REFRESH_TOKEN_MAX_AGE = 60 * 60 * 24 * 30;

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
