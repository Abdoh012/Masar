"use client";

import { Label } from "@/shared/components/ui/label";
import { Input } from "@/shared/components/ui/input";

interface LabeledInputProps {
  label: string;
  htmlFor: string;
  name: string;
  type?: string;
  defaultValue?: string | number;
  placeholder?: string;
  disabled?: boolean;
  maxLength?: number;
  min?: string | number;
  max?: string | number;
}

// LabeledInput: a reusable label + input pair with consistent spacing.
// Keeps the repeated <Label> + <Input> markup in one place instead of
// duplicating it per field.
export function LabeledInput({
  label,
  htmlFor,
  name,
  type = "text",
  defaultValue,
  placeholder,
  disabled,
  maxLength,
  min,
  max,
}: LabeledInputProps) {
  return (
    <div className="space-y-1.5">
      <Label htmlFor={htmlFor}>{label}</Label>
      <Input
        id={htmlFor}
        name={name}
        type={type}
        defaultValue={defaultValue}
        placeholder={placeholder}
        disabled={disabled}
        maxLength={maxLength}
        min={min}
        max={max}
      />
    </div>
  );
}
