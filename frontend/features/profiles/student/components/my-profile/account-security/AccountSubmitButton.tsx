"use client";

import { Loader2 } from "lucide-react";
import { useFormStatus } from "react-dom";

import { Button } from "@/shared/components/ui/button";

interface AccountSubmitButtonProps {
  label: string;
  pendingLabel: string;
  disabled?: boolean;
}

export function AccountSubmitButton({
  label,
  pendingLabel,
  disabled = false,
}: AccountSubmitButtonProps) {
  const { pending } = useFormStatus();

  return (
    <Button
      type="submit"
      size="sm"
      disabled={disabled || pending}
      className="cursor-pointer"
    >
      {pending ? (
        <>
          <Loader2 className="size-4 animate-spin" />
          {pendingLabel}
        </>
      ) : (
        label
      )}
    </Button>
  );
}
