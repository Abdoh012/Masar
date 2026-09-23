"use server";

import { revalidatePath } from "next/cache";

import { serverFetch } from "@/services/api";
import type { ActionState } from "@/types/server-action";

// requestCertificate: requests a certificate for an eligible completed training
// via POST /api/v1/certificates. Pure relay — sends the training_id carried by
// the eligible feed (never hardcoded) and lets the backend own all validation.
// revalidatePath("/certificates") re-renders the page server-side, so a
// requested training drops out of the eligible feed (the backend's NOT EXISTS
// rule) and the summary counts update — no client-side list state to sync.
export async function requestCertificate(
  trainingId: number,
): Promise<ActionState> {
  const result = await serverFetch({
    url: "certificates",
    method: "POST",
    body: { training_id: trainingId },
  });

  if (!result.success) {
    return {
      success: false,
      error: result.error ?? "Could not request the certificate.",
      fieldErrors: result.errors,
    };
  }

  revalidatePath("/certificates");
  return { success: true, message: result.message };
}