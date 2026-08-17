"use client";

import { useState } from "react";

import { Label } from "@/shared/components/ui/label";
import { Input } from "@/shared/components/ui/input";

import { Eye, EyeOff } from "lucide-react";

interface FormFieldProps {
  name: string;
  label: string;
  type: string;
  placeholder: string;
  defaultValue?: string;
}

export function FormField({
  name,
  label,
  type,
  placeholder,
  defaultValue,
}: FormFieldProps) {
  const [visible, setVisible] = useState(false);

  return (
    <div className="space-y-1.5 first:mb-5">
      <div className="flex items-center justify-between gap-2">
        <Label htmlFor={name}>{label}</Label>
      </div>

      <div className="relative">
        <Input
          name={name}
          type={type === "password" && visible ? "text" : type}
          placeholder={placeholder}
          required
          defaultValue={defaultValue}
        />

        {type === "password" ? (
          <button
            type="button"
            onClick={() => setVisible((prev) => !prev)}
            className="absolute cursor-pointer right-3 top-1/2 -translate-y-1/2 rounded text-muted-foreground transition-colors hover:text-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring/50"
          >
            {visible ? (
              <EyeOff className="h-4 w-4" />
            ) : (
              <Eye className="h-4 w-4" />
            )}
          </button>
        ) : null}
      </div>
    </div>
  );
}
