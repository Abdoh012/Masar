"use client";

import { Loader2 } from "lucide-react";

import { Label } from "@/shared/components/ui/label";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/shared/components/ui/select";
import { FieldErrorList } from "../../shared/components/FieldErrorList";

type SelectOption = string | { value: string; label: string };

interface ProfileSelectFieldProps {
  name: string;
  label: string;
  placeholder: string;
  optional?: boolean;
  defaultValue?: string;
  value?: string;
  onValueChange?: (value: string) => void;
  disabled?: boolean;
  options: SelectOption[];
  loading?: boolean;
  error?: string | null;
  errors?: string[];
}

export function ProfileSelectField(field: ProfileSelectFieldProps) {
  const isControlled = field.value !== undefined && field.onValueChange !== undefined;  

  return (
    <div className="space-y-1.5">
      <div className="flex items-center justify-between gap-2">
        <Label>{field.label}</Label>
        {field.optional ? (
          <span className="text-xs text-muted-foreground">(optional)</span>
        ) : null}
      </div>

      <Select
        key={isControlled ? undefined : field.defaultValue ?? ""}
        {...(isControlled
          ? { value: field.value, onValueChange: field.onValueChange }
          : { defaultValue: field.defaultValue })}
        name={field.name}
      >
        <SelectTrigger
          className="h-10 cursor-pointer"
          disabled={field.disabled || field.loading || !!field.error}
        >
          <SelectValue
            placeholder={field.loading ? "Loading..." : field.placeholder}
          />
        </SelectTrigger>

        <SelectContent>
          {field.options.map((option) => {
            const optValue = typeof option === "string" ? option : option.value;
            const optLabel = typeof option === "string" ? option : option.label;
            return (
              <SelectItem key={optValue} value={optValue} className="cursor-pointer">
                {optLabel}
              </SelectItem>
            );
          })}
        </SelectContent>
      </Select>

      {field.loading && !field.error ? (
        <p className="flex items-center gap-1.5 text-xs text-muted-foreground">
          <Loader2 className="size-3 animate-spin" />
          Loading options…
        </p>
      ) : field.error ? (
        <p className="text-xs text-destructive">{field.error}</p>
      ) : null}

      {!field.loading && !field.error && field.options.length === 0 ? (
        <p className="text-xs text-destructive">
          No options available. Please try again later.
        </p>
      ) : null}

      <FieldErrorList errors={field.errors} />
    </div>
  );
}
