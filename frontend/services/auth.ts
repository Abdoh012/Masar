"use server";

import { redirect } from "next/navigation";

import { serverFetch } from "./api";
import { deleteCookie, getCookie } from "./cookies";
import type { Session, Role, CompanyStatus } from "@/types/auth";

// Reads the session straight from cookies for use in server components/actions.
// Mirrors what middleware.ts checks, but middleware can't import "server-only"
// code, so this is a separate, page-side read.
export async function getSession(): Promise<Session | null> {
  const token = await getCookie("masarJwt");
  const role = (await getCookie("masarRole")) as Role | undefined;
  const companyStatus = (await getCookie("companyStatus")) as CompanyStatus | undefined;

  if (!token || !role) return null;

  return { token, role, companyStatus };
}

export async function logout() {
  await serverFetch({ url: "auth/logout", method: "POST", body: {} });

  await deleteCookie("masarJwt");
  await deleteCookie("masarRole");
  await deleteCookie("companyStatus");

  redirect("/sign-in");
}