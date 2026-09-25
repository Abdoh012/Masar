"use client";

import { AtSign, Info } from "lucide-react";

import { Button } from "@/shared/components/ui/button";
import { Input } from "@/shared/components/ui/input";
import { Label } from "@/shared/components/ui/label";

import { ACCOUNT_SECURITY_LABELS } from "./constants";

interface UsernameFieldProps {
  defaultValue: string;
}

// UsernameField: the username form. Uncontrolled — the input holds the value
// and nothing re-renders on each keystroke. The submit handler is deliberately
// inert: there is no profile API, so a real <form action> would only pretend to
// save (and would reset the field). `preventDefault` keeps the semantics of a
// form (Enter submits, the button is type="submit") without the round trip.
export function UsernameField({ defaultValue }: UsernameFieldProps) {
  return (
    <form
      className="space-y-4"
      onSubmit={(event) => {
        // TODO: connect to backend — hand this to the updateUsername server
        // action (student/actions.ts) and revalidatePath("/profile").
        event.preventDefault();
      }}
    >
      <div className="space-y-1.5">
        <Label htmlFor="profile-username">{ACCOUNT_SECURITY_LABELS.username.label}</Label>

        <div className="relative">
          <AtSign className="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />

          <Input
            id="profile-username"
            name="username"
            type="text"
            defaultValue={defaultValue}
            placeholder={ACCOUNT_SECURITY_LABELS.username.placeholder}
            className="pl-10"
          />
        </div>
      </div>

      <p className="flex items-start gap-1.5 text-xs leading-relaxed text-muted-foreground">
        <Info className="mt-0.5 size-3.5 shrink-0" />
        {ACCOUNT_SECURITY_LABELS.username.pending}
      </p>

      <div className="flex justify-end">
        <Button type="submit" size="sm" className="cursor-pointer">
          {ACCOUNT_SECURITY_LABELS.username.save}
        </Button>
      </div>
    </form>
  );
}
