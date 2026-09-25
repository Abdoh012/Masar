"use client";

import { useState } from "react";
import { Eye, EyeOff } from "lucide-react";

import { Input } from "@/shared/components/ui/input";
import { Label } from "@/shared/components/ui/label";
import { cn } from "@/shared/lib/utils";

interface PasswordFieldProps {
  id: string;
  label: string;
  value: string;
  onChange: (value: string) => void;
  invalid?: boolean;
}

// PasswordField: a labelled password control that owns its own reveal toggle, so
// the two fields in the password form don't each have to repeat it. Visibility
// is local state; the input's type is driven by it.
export function PasswordField({
  id,
  label,
  value,
  onChange,
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
          type={isRevealed ? "text" : "password"}
          value={value}
          aria-invalid={invalid}
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
