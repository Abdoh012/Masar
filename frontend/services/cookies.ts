import "server-only";
import { cookies } from "next/headers";

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
