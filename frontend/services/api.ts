"use server";

import { cookies } from "next/headers";
import type { TryCatchRequest, TryCatchResponse } from "@/types/server-action";

const API_URL =
  process.env.NEXT_PUBLIC_API_URL ?? "http://localhost:5000/api/v1";

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

    const headers: Record<string, string> = {
      "Content-Type": "application/json",
    };

    if (token) {
      headers["Authorization"] = `Bearer ${token}`;
    }

    const fetchOptions: RequestInit & { next?: { revalidate: number } } = {
      method,
      headers,
      body: body ? JSON.stringify(body) : undefined,
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
        userData: body,
      };
    }

    const resData = await res.json();
    return { success: true, data: resData, message: resData.message };
  } catch {
    return {
      success: false,
      error: "Unable to reach the server, please try again later",
    };
  }
}
