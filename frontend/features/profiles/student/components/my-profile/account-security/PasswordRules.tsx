"use client";

import { Check, Minus } from "lucide-react";

import { cn } from "@/shared/lib/utils";

import { PASSWORD_RULES } from "./constants";
import type { PasswordRuleId } from "./constants";

interface PasswordRulesProps {
  met: Record<PasswordRuleId, boolean>;
}

// PasswordRules: the live requirement checklist under the password fields. The
// form owns the state and resolves one boolean per rule id; this leaf only maps
// them onto PASSWORD_RULES for the labels, so the checklist, the submit gate and
// the backend policy can never drift apart. Requirements are shown from the
// first keystroke — a submit-gated error would say nothing until the user
// already got it wrong.
export function PasswordRules({ met }: PasswordRulesProps) {
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
