"use server";

import type { TryCatchRequest, TryCatchResponse } from "@/types/server-action";
import {
  ACCESS_TOKEN_COOKIE,
  COMPANY_STATUS_COOKIE,
  ROLE_COOKIE,
} from "./cookies";
import { deleteCookie, getCookie } from "./cookies";

const API_URL =
  process.env.NEXT_PUBLIC_API_URL ?? "http://localhost:8000/api/v1";

export async function serverFetch({
  url,
  method = "GET",
  body,
  cache = "default",
  revalidate,
}: TryCatchRequest): Promise<TryCatchResponse> {
  try {
    const isFormData = body instanceof FormData;

    const doFetch = async (): Promise<Response> => {
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

      return fetch(`${API_URL}/${url}`, fetchOptions);
    };

    const res: Response = await doFetch();

    if (!res.ok) {
      // Error bodies aren't guaranteed JSON (e.g. a gateway 502/504 may return
      // an HTML page); parse defensively so a non-JSON body still yields a
      // proper error message rather than a network failure.
      const data = await res.json().catch(() => null);
      return {
        success: false,
        error: data?.message || "Invalid data, please try again later",
        errors: data?.errors || undefined,
        userData: body,
        status: res.status,
      };
    }

    // Success body.
    const resData: any = await res.json();

    return {
      success: true,
      data: resData.data,
      message: resData.message,
    };
  } catch (error) {
    return {
      success: false,
      error: "Unable to reach the server, please try again later",
    };
  }
}
