"use client";

import { Check, Minus } from "lucide-react";

import { cn } from "@/shared/lib/utils";

import { PASSWORD_RULES } from "./constants";

interface PasswordRulesProps {
  minLengthMet: boolean;
  matches: boolean;
}

// PasswordRules: the live requirement checklist under the password fields. It
// takes the two resolved booleans (the form owns the state, this leaf owns
// nothing but the display) and maps them onto PASSWORD_RULES for the labels, so
// the checklist and the validation can never drift apart. Requirements are shown
// from the first keystroke — a submit-gated error would say nothing until the
// user already got it wrong.
export function PasswordRules({ minLengthMet, matches }: PasswordRulesProps) {
  const met: Record<string, boolean> = { length: minLengthMet, match: matches };

  return (
    <ul className="space-y-1.5">
      {PASSWORD_RULES.map((rule) => {
        const isMet = met[rule.id];

        return (
          <li key={rule.id} className="flex items-center gap-2 text-xs">
            <span
              className={cn(
                "grid size-4 shrink-0 place-items-center rounded-full",
                isMet
                  ? "bg-success-bg text-success-fg"
                  : "bg-neutral-badge-bg text-neutral-badge-fg",
              )}
            >
              {isMet ? (
                <Check className="size-2.5" />
              ) : (
                <Minus className="size-2.5" />
              )}
            </span>

            <span className={isMet ? "text-muted-foreground" : "text-disabled"}>
              {rule.label}
            </span>
          </li>
        );
      })}
    </ul>
  );
}
