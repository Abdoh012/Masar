"use client";

import { useState } from "react";
import { Eye, EyeOff } from "lucide-react";

import { Input } from "@/shared/components/ui/input";
import { Label } from "@/shared/components/ui/label";
import { cn } from "@/shared/lib/utils";

interface PasswordFieldProps {
  id: string;
  /** Backend field name — the surrounding form sends FormData, never a payload. */
  name: string;
  label: string;
  value: string;
  onChange: (value: string) => void;
  autoComplete?: string;
  invalid?: boolean;
}

// PasswordField: a labelled password control that owns its own reveal toggle, so
// the three fields in the password form don't each have to repeat it. Visibility
// is local state; the input's type is driven by it. The value stays controlled
// because the form's live checklist and confirm-match check read it — the
// documented per-field exception in structure rules §10.
export function PasswordField({
  id,
  name,
  label,
  value,
  onChange,
  autoComplete,
  invalid = false,
}: PasswordFieldProps) {
  const [isRevealed, setIsRevealed] = useState(false);
  const RevealIcon = isRevealed ? EyeOff : Eye;

  return (
    <div className="space-y-1.5">
      <Label htmlFor={id}>{label}</Label>

      <div className="relative">
        <Input
          id={id}
          name={name}
          type={isRevealed ? "text" : "password"}
          value={value}
          autoComplete={autoComplete}
          aria-invalid={invalid}
          required
          onChange={(event) => onChange(event.target.value)}
          className={cn(invalid && "border-destructive")}
        />

        <button
          type="button"
          onClick={() => setIsRevealed((revealed) => !revealed)}
          className="absolute right-3 top-1/2 -translate-y-1/2 cursor-pointer text-muted-foreground transition-colors hover:text-primary-text focus-visible:outline-none"
          aria-label={isRevealed ? "Hide password" : "Show password"}
        >
          <RevealIcon className="size-4" />
        </button>
      </div>
    </div>
  );
}
