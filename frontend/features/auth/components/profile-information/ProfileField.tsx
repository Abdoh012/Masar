"use client";

import { Label } from "@/shared/components/ui/label";
import { Input } from "@/shared/components/ui/input";
import { FieldErrorList } from "../../shared/components/FieldErrorList";

interface ProfileFieldProps {
  name: string;
  label: string;
  placeholder: string;
  optional?: boolean;
  defaultValue?: string;
  errors?: string[];
}

export function ProfileField({
  name,
  label,
  placeholder,
  optional = false,
  defaultValue,
  errors,
}: ProfileFieldProps) {
  return (
    <div className="space-y-1.5">
      <div className="flex items-center justify-between gap-2">
        <Label htmlFor={name}>{label}</Label>
        {optional ? (
          <span className="text-xs text-muted-foreground">(optional)</span>
        ) : null}
      </div>

      <Input
        name={name}
        type="text"
        placeholder={placeholder}
        required={!optional}
        defaultValue={defaultValue}
      />

      <FieldErrorList errors={errors} />
    </div>
  );
}