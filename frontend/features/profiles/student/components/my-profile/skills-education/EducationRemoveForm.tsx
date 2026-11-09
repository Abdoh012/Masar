"use client";

import { useRef } from "react";

import { Trash2 } from "lucide-react";

import { Button } from "@/shared/components/ui/button";
import { useFormFeedBack } from "@/shared/hooks/useFormFeedback";

import { removeEducation } from "../../../actions";
import type { EducationEntry } from "../../../types";
import { EDUCATION_LABELS } from "./constants";

export function EducationRemoveForm({ entry }: { entry: EducationEntry }) {
  const { formAction, isPending } = useFormFeedBack(removeEducation, null);

  return (
    <>
      <form action={formAction}>
        <input type="hidden" name="education_id" value={entry.id} />

        <Button
          type="submit"
          variant="ghost"
          size="icon"
          className="size-8 shrink-0 text-muted-foreground hover:text-error-fg"
          disabled={isPending}
          aria-label={EDUCATION_LABELS.remove}
        >
          <Trash2 className="size-4" />
        </Button>
      </form>
    </>
  );
}
