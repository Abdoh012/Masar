import { cookies } from "next/headers";

import { serverFetch } from "@/services/api";
import { ROLE_HOME } from "@/config/routes";

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
    return {
      error: result.error,
      fieldErrors: result.errors,
      userData: result.userData,
    };
  }

  const token = result.data?.token;
  const role = result.data?.user?.role;
  const userStatus = result.data?.user?.status;

  const cookieStore = await cookies();

  if (token && role) {
    cookieStore.set("masarJwt", token, {
      httpOnly: true,
      sameSite: "lax",
      maxAge: 60 * 60 * 24 * 90,
      path: "/",
    });

    cookieStore.set("masarRole", role, {
      httpOnly: true,
      sameSite: "lax",
      maxAge: 60 * 60 * 24 * 90,
      path: "/",
    });

    if (role === "company") {
      cookieStore.set("companyStatus", userStatus, {
        httpOnly: true,
        sameSite: "lax",
        maxAge: 60 * 60 * 24 * 90,
        path: "/",
      });
    }
  }

  return {
    success: true,
    message: result.message,
    role,
    userStatus,
    redirectPath: role ? ROLE_HOME[role as keyof typeof ROLE_HOME] : undefined,
  };
}
