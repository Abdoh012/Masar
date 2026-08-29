"use server";

import { redirect } from "next/navigation";

import { serverFetch } from "./api";
import {
  ACCESS_TOKEN_COOKIE,
  COMPANY_STATUS_COOKIE,
  CSRF_TOKEN_COOKIE,
  deleteCookie,
  getCookie,
  REFRESH_TOKEN_COOKIE,
  ROLE_COOKIE,
} from "./cookies";
import type { Session, Role, CompanyStatus } from "@/types/auth";

// Reads the session straight from cookies for use in server components/actions.
// Mirrors what middleware.ts checks, but middleware can't import "server-only"
// code, so this is a separate, page-side read.
export async function getSession(): Promise<Session | null> {
  const token = await getCookie(ACCESS_TOKEN_COOKIE);
  const role = (await getCookie(ROLE_COOKIE)) as Role | undefined;
  const companyStatus = (await getCookie(COMPANY_STATUS_COOKIE)) as CompanyStatus | undefined;

  if (!token || !role) return null;

  return { token, role, companyStatus };
}

export async function logout() {
  await serverFetch({ url: "auth/logout", method: "POST", body: {} });

  await deleteCookie(ACCESS_TOKEN_COOKIE);
  await deleteCookie(ROLE_COOKIE);
  await deleteCookie(COMPANY_STATUS_COOKIE);
  await deleteCookie(REFRESH_TOKEN_COOKIE);
  await deleteCookie(CSRF_TOKEN_COOKIE);

  redirect("/sign-in");
}