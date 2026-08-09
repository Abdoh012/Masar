"use client";

import { useState, type FormEvent } from "react";

import { Button } from "@/shared/components/ui/button";
import { Input } from "@/shared/components/ui/input";
import { Label } from "@/shared/components/ui/label";
import { cn } from "@/shared/lib/utils";
import { CONTACT_FORM } from "./contact-form.content";

// ContactForm: interactive client leaf (FR-015/026). Uncontrolled inputs
// (name/email/message) validated client-side; a valid submit shows a
// simulated success panel and resets the form. No server, no persistence.
export function ContactForm() {
  const [submitted, setSubmitted] = useState(false);
  const [errors, setErrors] = useState<Record<string, string>>({});

  function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    const formData = new FormData(event.currentTarget);
    const nextErrors: Record<string, string> = {};

    for (const field of CONTACT_FORM.fields) {
      const value = String(formData.get(field.name) ?? "").trim();
      if (field.required && !value) {
        nextErrors[field.name] = "This field is required.";
        continue;
      }
      if (field.type === "email" && value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
        nextErrors[field.name] = "Please enter a valid email address.";
      }
    }

    if (Object.keys(nextErrors).length > 0) {
      setErrors(nextErrors);
      return;
    }

    setErrors({});
    setSubmitted(true);
    event.currentTarget.reset();
  }

  return (
    <div className="w-full rounded-2xl border border-border bg-card p-6 shadow-card sm:p-8">
      {submitted ? (
        <div
          role="status"
          className="flex flex-col items-start gap-2 rounded-xl bg-success-bg p-6 text-success-fg"
        >
          <p className="text-lg font-semibold">{CONTACT_FORM.successTitle}</p>
          <p className="text-sm leading-relaxed">{CONTACT_FORM.successBody}</p>
        </div>
      ) : (
        <form onSubmit={handleSubmit} className="flex flex-col gap-5" noValidate>
          {CONTACT_FORM.fields.map((field) => {
            const error = errors[field.name];
            const isTextarea = field.type === "textarea";
            const fieldId = `contact-${field.name}`;
            return (
              <div key={field.name} className="flex flex-col gap-2">
                <Label htmlFor={fieldId}>
                  {field.label}
                  {field.required ? (
                    <span className="text-error-500" aria-hidden="true">
                      {" "}
                      *
                    </span>
                  ) : null}
                </Label>
                {isTextarea ? (
                  <textarea
                    id={fieldId}
                    name={field.name}
                    rows={4}
                    placeholder={field.placeholder}
                    aria-invalid={Boolean(error)}
                    aria-describedby={error ? `${fieldId}-error` : undefined}
                    className={cn(
                      "w-full rounded-md border border-input bg-card px-3 py-2 text-sm text-foreground transition-[border-color,box-shadow] duration-200 ring-2 ring-transparent placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring",
                      error && "border-error-500 focus-visible:ring-error-500",
                    )}
                  />
                ) : (
                  <Input
                    id={fieldId}
                    name={field.name}
                    type={field.type}
                    placeholder={field.placeholder}
                    aria-invalid={Boolean(error)}
                    aria-describedby={error ? `${fieldId}-error` : undefined}
                    className={cn(
                      "w-full",
                      error && "border-error-500 focus-visible:ring-error-500",
                    )}
                  />
                )}
                {error ? (
                  <p id={`${fieldId}-error`} className="text-sm text-error-500" role="alert">
                    {error}
                  </p>
                ) : null}
              </div>
            );
          })}
          <Button type="submit" className="w-full cursor-pointer">
            {CONTACT_FORM.submitLabel}
          </Button>
        </form>
      )}
    </div>
  );
}