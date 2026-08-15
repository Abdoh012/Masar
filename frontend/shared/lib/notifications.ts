import type { CSSProperties } from "react";

import { toast, type ExternalToast } from "sonner";

// Shared toast options applied to every notification.
// Keeps all call sites consistent; position/closeButton/duration are centralized here.
const TOAST_BASE_OPTIONS = {
  duration: 5000,
  position: "bottom-right",
  closeButton: true,
} as const;

type ToastVariant = "success" | "error" | "info";

// Per-variant styles using Masar semantic tokens (theme-aware via CSS vars).
// Light/dark values come from the token definitions, not hardcoded hexes.
const TOAST_STYLES: Record<ToastVariant, CSSProperties> = {
  success: {
    backgroundColor: "var(--color-success-bg)",
    color: "var(--color-success-fg)",
    border: "1px solid var(--color-success-500)",
  },
  error: {
    backgroundColor: "var(--color-error-bg)",
    color: "var(--color-error-fg)",
    border: "1px solid var(--color-error-500)",
  },
  info: {
    backgroundColor: "var(--color-info-bg)",
    color: "var(--color-info-fg)",
    border: "1px solid var(--color-info-500)",
  },
};

const TOAST_CALLS: Record<
  ToastVariant,
  (message: string, data?: ExternalToast) => string | number
> = {
  success: toast.success,
  error: toast.error,
  info: toast.info,
};

function notify(variant: ToastVariant, message: string) {
  TOAST_CALLS[variant](message, {
    ...TOAST_BASE_OPTIONS,
    style: TOAST_STYLES[variant],
  });
}

// Show a success message.
export function showSuccess(message: string) {
  notify("success", message);
}

// Show an error message.
export function showError(message: string) {
  notify("error", message);
}

// Show an informational message.
export function showInfo(message: string) {
  notify("info", message);
}