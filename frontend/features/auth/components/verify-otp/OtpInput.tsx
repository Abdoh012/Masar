"use client";

import { useRef, useState } from "react";
import type { ClipboardEvent, KeyboardEvent } from "react";

import { Input } from "@/shared/components/ui/input";
import { cn } from "@/shared/lib/utils";

import { OTP_LENGTH } from "../../shared/lib/constants";

export function OtpInput() {
  const [digits, setDigits] = useState<string[]>(() =>
    Array(OTP_LENGTH).fill(""),
  );
  const refs = useRef<Array<HTMLInputElement | null>>([]);

  const inputClass = cn(
    "h-12 w-11 rounded-lg border border-input bg-card text-center text-lg font-semibold tabular-nums text-foreground",
    "transition-[border-color,box-shadow] duration-200 ring-2 ring-transparent",
    "focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring",
  );

  function focusIndex(index: number) {
    refs.current[index]?.focus();
  }

  function handleChange(index: number, value: string) {
    const digit = value.replace(/\D+/g, "").slice(-1);
    const next = [...digits];
    next[index] = digit;
    setDigits(next);

    if (digit && index < OTP_LENGTH - 1) {
      focusIndex(index + 1);
    }
  }

  function handleKeyDown(
    index: number,
    event: KeyboardEvent<HTMLInputElement>,
  ) {
    if (event.key === "Backspace" && !digits[index] && index > 0) {
      focusIndex(index - 1);
    }
  }

  function handlePaste(event: ClipboardEvent<HTMLInputElement>) {
    event.preventDefault();
    const pasted = event.clipboardData
      .getData("text")
      .replace(/\D+/g, "")
      .slice(0, OTP_LENGTH)
      .split("");

    if (!pasted.length) return;

    setDigits(pasted);
    focusIndex(Math.min(pasted.length, OTP_LENGTH - 1));
  }

  return (
    <div
      className="flex justify-center gap-2 sm:gap-3"
      role="group"
      aria-label="Verification code"
    >
      <input type="hidden" name="token" value={digits.join("")} />
      {Array.from({ length: OTP_LENGTH }, (_, index) => (
        <Input
          key={index}
          ref={(node) => {
            refs.current[index] = node;
          }}
          inputMode="numeric"
          autoComplete={index === 0 ? "one-time-code" : "off"}
          aria-label={`Digit ${index + 1}`}
          value={digits[index]}
          onChange={(event) => handleChange(index, event.target.value)}
          onKeyDown={(event) => handleKeyDown(index, event)}
          onPaste={handlePaste}
          className={inputClass}
          maxLength={1}
        />
      ))}
    </div>
  );
}
