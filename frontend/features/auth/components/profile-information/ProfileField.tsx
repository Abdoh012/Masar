"use client";

import { Label } from "@/shared/components/ui/label";
import { Input } from "@/shared/components/ui/input";

interface ProfileFieldProps {
  name: string;
  label: string;
  placeholder: string;
  optional?: boolean;
}

export function ProfileField({
  name,
  label,
  placeholder,
  optional = false,
}: ProfileFieldProps) {
  return (
    <div className="space-y-1.5">
      <div className="flex items-center justify-between gap-2">
        <Label htmlFor={name}>{label}</Label>
        {optional ? (
          <span className="text-xs text-muted-foreground">(optional)</span>
        ) : null}
      </div>

      <Input name={name} type="text" placeholder={placeholder} required={!optional} />
    </div>
  );
}