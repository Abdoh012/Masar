import { getCookie } from "./cookies";
import { env } from "@/config/env";

interface RequestOptions extends RequestInit {
  // Set to false for endpoints that don't need the JWT attached
  auth?: boolean;
}

// Base fetch wrapper every feature's api/ layer should go through (R9/R10).
// Handles auth header attachment and empty-body responses (e.g. DELETE)
// the same way the el-le3ba serverFetch fix does — don't JSON.parse("").
export async function apiFetch<T>(path: string, options: RequestOptions = {}): Promise<T> {
  const { auth = true, headers, ...rest } = options;

  const finalHeaders: Record<string, string> = {
    "Content-Type": "application/json",
    ...(headers as Record<string, string>),
  };

  if (auth) {
    const token = await getCookie("jwt");
    if (token) finalHeaders.Authorization = `Bearer ${token}`;
  }

  const res = await fetch(`${env.apiUrl}${path}`, { ...rest, headers: finalHeaders });

  if (!res.ok) {
    let message = `Request failed with status ${res.status}`;
    try {
      const body = await res.json();
      message = body?.message ?? message;
    } catch {
      // body wasn't JSON — keep the default message
    }
    throw new Error(message);
  }

  const text = await res.text();
  if (!text) return { success: true } as T;
  return JSON.parse(text) as T;
}
