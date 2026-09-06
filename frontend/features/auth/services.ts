import { cookies } from "next/headers";

import { serverFetch } from "@/services/api";
import {
  ACCESS_TOKEN_COOKIE,
  ACCESS_TOKEN_MAX_AGE,
  COMPANY_STATUS_COOKIE,
  CSRF_TOKEN_COOKIE,
  REFRESH_TOKEN_COOKIE,
  REFRESH_TOKEN_MAX_AGE,
  ROLE_COOKIE,
} from "@/services/cookies";
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
    cookieStore.set(ACCESS_TOKEN_COOKIE, token, {
      httpOnly: true,
      sameSite: "lax",
      maxAge: ACCESS_TOKEN_MAX_AGE,
      path: "/",
    });

    cookieStore.set(ROLE_COOKIE, role, {
      httpOnly: true,
      sameSite: "lax",
      maxAge: ACCESS_TOKEN_MAX_AGE,
      path: "/",
    });

    if (role === "company") {
      cookieStore.set(COMPANY_STATUS_COOKIE, userStatus, {
        httpOnly: true,
        sameSite: "lax",
        maxAge: ACCESS_TOKEN_MAX_AGE,
        path: "/",
      });
    }

    // The backend mints refresh_token + csrf_token cookies on login; persist
    // them so an expired access token can later be refreshed server-side.
    for (const { name, value } of result.cookies ?? []) {
      if (name === REFRESH_TOKEN_COOKIE || name === CSRF_TOKEN_COOKIE) {
        cookieStore.set(name, value, {
          httpOnly: true,
          sameSite: "lax",
          maxAge: REFRESH_TOKEN_MAX_AGE,
          path: "/",
        });
      }
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
