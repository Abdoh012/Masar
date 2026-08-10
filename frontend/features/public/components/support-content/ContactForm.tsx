"use client";

import { useState, type FormEvent } from "react";

import { Button } from "@/shared/components/ui/button";
import { Input } from "@/shared/components/ui/input";
import { Label } from "@/shared/components/ui/label";
import { cn } from "@/shared/lib/utils";
import { CONTACT_FORM } from "./contact-form.content";

export function ContactForm() {
  return (
    <div className="w-full rounded-2xl border border-border bg-card p-6 shadow-card sm:p-8">
      {/* <div className="flex flex-col items-start gap-2 rounded-xl bg-success-bg p-6 text-success-fg">
        <p className="text-lg font-semibold">{CONTACT_FORM.successTitle}</p>
        <p className="text-sm leading-relaxed">{CONTACT_FORM.successBody}</p>
      </div> */}

      <form className="flex flex-col gap-5">
        {CONTACT_FORM.fields.map((field) => {
          const isTextarea = field.type === "textarea";

          return (
            <div key={field.name} className="flex flex-col gap-2">
              <Label htmlFor={field.name}>{field.label}</Label>

              {/* textarea */}
              {isTextarea ? (
                <textarea
                  id={field.name}
                  name={field.name}
                  placeholder={field.placeholder}
                  className="w-full rounded-md border border-input bg-card px-3 py-2 text-sm text-foreground duration-200 placeholder:text-muted-foreground transition-[border-color,box-shadow] ring-2 ring-transparent focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                />
              ) : (
                <Input
                  id={field.name}
                  name={field.name}
                  type={field.type}
                  placeholder={field.placeholder}
                  className="w-full"
                />
              )}
            </div>
          );
        })}

        {/* submit button */}
        <Button type="submit" className="w-full cursor-pointer">
          {CONTACT_FORM.submitLabel}
        </Button>
      </form>
    </div>
  );
}
