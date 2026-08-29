"use server";

import { cookies } from "next/headers";
import type { TryCatchRequest, TryCatchResponse } from "@/types/server-action";

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
    const cookieStore = await cookies();
    const token = cookieStore.get("masarJwt")?.value;

    // Multipart bodies (FormData) must not set Content-Type — fetch generates
    // the multipart boundary itself; everything else rides JSON.
    const isFormData = body instanceof FormData;

    const headers: Record<string, string> = {};

    if (!isFormData) {
      headers["Content-Type"] = "application/json";
    }

    if (token) {
      headers["Authorization"] = `Bearer ${token}`;
    }

    const fetchOptions: RequestInit & { next?: { revalidate: number } } = {
      method,
      headers,
      body: isFormData ? body : body ? JSON.stringify(body) : undefined,
      cache,
    };

    if (cache !== "no-store" && revalidate !== undefined) {
      fetchOptions.next = { revalidate };
    }

    const res = await fetch(`${API_URL}/${url}`, fetchOptions);

    if (!res.ok) {
      const data = await res.json();
      return {
        error: data.message || "Invalid data, please try again later",
        errors: data.errors || undefined,
        userData: body,
      };
    }

    const resData = await res.json();
    return {
      success: true,
      data: resData.data,
      message: resData.message,
    };
  } catch {
    return {
      success: false,
      error: "Unable to reach the server, please try again later",
    };
  }
}
