"use client";

import type { LucideIcon } from "lucide-react";

import { Input } from "@/shared/components/ui/input";
import { Label } from "@/shared/components/ui/label";
import { cn } from "@/shared/lib/utils";

interface ProfileFieldInputProps {
  id: string;
  name?: string;
  label: string;
  /** Controlled mode: pass both value and onChange. */
  value?: string;
  onChange?: (value: string) => void;
  /** Uncontrolled mode: used when no value is provided (form submissions). */
  defaultValue?: string;
  type?: "text" | "email" | "tel" | "url" | "month";
  placeholder?: string;
  icon?: LucideIcon;
  className?: string;
  min?: string;
  max?: string;
  required?: boolean;
}

export function ProfileFieldInput({
  id,
  name,
  label,
  value,
  onChange,
  defaultValue,
  type = "text",
  placeholder,
  icon: Icon,
  className,
  min,
  max,
  required,
}: ProfileFieldInputProps) {
  const isControlled = value !== undefined;

  return (
    <div className={cn("space-y-1.5", className)}>
      <Label htmlFor={id}>{label}</Label>

      <div className="relative">
        {Icon ? (
          <Icon className="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
        ) : null}

        <Input
          id={id}
          name={name}
          type={type}
          value={isControlled ? value : undefined}
          defaultValue={isControlled ? undefined : defaultValue}
          placeholder={placeholder}
          min={min}
          max={max}
          required={required}
          onChange={
            isControlled ? (event) => onChange?.(event.target.value) : undefined
          }
          className={cn(Icon && "pl-10")}
        />
      </div>
    </div>
  );
}
