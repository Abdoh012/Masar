"use client";

import { useEffect, useRef, useState } from "react";

import { Check, Copy } from "lucide-react";

import { PAYMENT_LABELS } from "./constants";

const COPIED_DURATION_MS = 1500;

// CopyAccountNumberButton: minimal client leaf isolating the only interaction
// in the bank transfer block — writes the account number to the clipboard and
// swaps the copy icon for a check for 1.5s (then reverts). Interactivity is
// pushed as low as possible (structure rules §8); everything else in the
// payment section stays a server component.
export function CopyAccountNumberButton({
  accountNumber,
}: {
  accountNumber: string;
}) {
  const [copied, setCopied] = useState(false);
  const timeoutRef = useRef<ReturnType<typeof setTimeout> | null>(null);

  useEffect(
    () => () => {
      if (timeoutRef.current) clearTimeout(timeoutRef.current);
    },
    [],
  );

  const handleCopy = async () => {
    try {
      await navigator.clipboard.writeText(accountNumber);
      setCopied(true);
      if (timeoutRef.current) clearTimeout(timeoutRef.current);
      timeoutRef.current = setTimeout(
        () => setCopied(false),
        COPIED_DURATION_MS,
      );
    } catch {
      // Clipboard access can be denied (permissions, non-secure context) —
      // stay in the copy state rather than surfacing an error.
    }
  };

  return (
    <button
      type="button"
      onClick={handleCopy}
      aria-label={
        copied
          ? PAYMENT_LABELS.accountNumberCopied
          : PAYMENT_LABELS.copyAccountNumber
      }
      className="inline-flex size-8 shrink-0 items-center justify-center cursor-pointer rounded-md text-muted-foreground transition-colors hover:bg-neutral-badge-bg hover:text-foreground"
    >
      {copied ? (
        <Check className="size-4 text-success-fg" aria-hidden />
      ) : (
        <Copy className="size-4" aria-hidden />
      )}
    </button>
  );
}