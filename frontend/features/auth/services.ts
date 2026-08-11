import { cookies } from "next/headers";

import { serverFetch } from "@/services/api";

import { AuthResponse } from "./types";

interface Props {
  url: string;
  body: object;
}

export async function authenticate({
  url,
  body,
}: Props): Promise<AuthResponse> {
  const result = await serverFetch({ url, method: "POST", body });

  if (!result.success) {
    return { error: result.error, userData: result.userData };
  }

  const token = result.data.auth?.token;
  const role = result.data.auth?.user?.role;

  const cookieStore = await cookies();

  // Set jwt token cookie for 90 days
  cookieStore.set("masarJwt", token, {
    httpOnly: true,
    sameSite: "lax",
    maxAge: 60 * 60 * 24 * 90,
    path: "/",
  });

  // Set user role cookie for 90 days
  cookieStore.set("masarRole", role, {
    httpOnly: true,
    sameSite: "lax",
    maxAge: 60 * 60 * 24 * 90,
    path: "/",
  });

  return {
    success: true,
    message: result.message,
    redirectPath: role === "admin" ? "/admin/dashboard" : "/dashboard",
  };
}
