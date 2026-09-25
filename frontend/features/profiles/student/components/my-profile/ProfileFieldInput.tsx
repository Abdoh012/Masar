"use client";

import type { LucideIcon } from "lucide-react";

import { Input } from "@/shared/components/ui/input";
import { Label } from "@/shared/components/ui/label";
import { cn } from "@/shared/lib/utils";

interface ProfileFieldInputProps {
  id: string;
  label: string;
  value: string;
  onChange: (value: string) => void;
  type?: "text" | "email" | "tel" | "url" | "month";
  placeholder?: string;
  icon?: LucideIcon;
  className?: string;
}

// ProfileFieldInput: the labelled text control both profile forms are built
// from — Label + Input, with an optional leading icon for the contact fields
// (icons sit inside the field so the labels stay plain). Controlled by its
// owner: each form keeps its own state, so no value is threaded through the
// page. `className` lets a caller span a field across a grid track.
export function ProfileFieldInput({
  id,
  label,
  value,
  onChange,
  type = "text",
  placeholder,
  icon: Icon,
  className,
}: ProfileFieldInputProps) {
  return (
    <div className={cn("space-y-1.5", className)}>
      <Label htmlFor={id}>{label}</Label>

      <div className="relative">
        {Icon ? (
          <Icon className="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
        ) : null}

        <Input
          id={id}
          type={type}
          value={value}
          placeholder={placeholder}
          onChange={(event) => onChange(event.target.value)}
          className={cn(Icon && "pl-10")}
        />
      </div>
    </div>
  );
}
