"use client";

import {
  saveTrainingAction,
  unsaveTrainingAction,
} from "@/features/listings/student/actions";
import { showError, showSuccess } from "@/shared/lib/notifications";
import { Bookmark, Loader2 } from "lucide-react";
import { useEffect, useState } from "react";

interface SaveButtonProps {
  saved: boolean | undefined;
  id: string;
  onUnsaved?: () => void;
}

export function SaveButton({ saved, id, onUnsaved }: SaveButtonProps) {
  const [isSaved, setIsSaved] = useState(saved ?? false);
  const [pending, setPending] = useState(false);

  useEffect(() => {
    setIsSaved(saved ?? false);
  }, [saved]);

  async function handleSaveToggle() {
    if (pending) return;

    const action = isSaved ? unsaveTrainingAction : saveTrainingAction;

    try {
      setPending(true);
      const result = await action(id);
      if (result.error) {
        showError(result.error || "Failed to save training");
        return;
      }

      setIsSaved((prev) => !prev);
      showSuccess(result.message || "Training saved successfully");
      if (isSaved) onUnsaved?.();
    } catch (error) {
      showError("Failed to save training");
    } finally {
      setPending(false);
    }
  }

  return (
    <button
      type="button"
      onClick={handleSaveToggle}
      disabled={pending}
      className={`cursor-pointer rounded-md p-1.5 transition-colors disabled:opacity-50 ${
        isSaved
          ? "text-secondary hover:bg-secondary-tint"
          : "text-muted-foreground hover:bg-primary-tint hover:text-primary"
      }`}
    >
      {pending ? (
        <Loader2 className="size-5 animate-spin" />
      ) : (
        <Bookmark className={`size-5 ${isSaved ? "fill-current" : ""}`} />
      )}
    </button>
  );
}
