"use client";

import { useEffect } from "react";

import { showError } from "@/shared/lib/notifications";

interface SessionExpiredToastProps {
  sessionExpired: boolean;
  error: string;
}

// SessionExpiredToast: shows an error toast when the user lands on sign-in via
// the session-expired redirect (serverFetch bounces an unrecoverable 401
// here). After firing it strips the sessionExpired/error query params from the
// URL, so a page reload lands on a clean sign-in instead of re-firing the
// toast. The props come from the redirect's query params, not something the
// form ever re-triggers.
export function SessionExpiredToast({
  sessionExpired,
  error,
}: SessionExpiredToastProps) {
  useEffect(() => {
    if (sessionExpired) {
      showError(error || "Your session has expired. Please sign in again.");

      const url = new URL(window.location.href);
      url.searchParams.delete("sessionExpired");
      url.searchParams.delete("error");
      window.history.replaceState(null, "", url.toString());
    }
  }, [sessionExpired, error]);

  return null;
}