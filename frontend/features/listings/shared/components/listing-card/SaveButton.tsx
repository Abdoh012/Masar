"use client";

import { useTransition } from "react";

import {
  saveTrainingAction,
  unsaveTrainingAction,
} from "@/features/listings/student/actions";
import { showError, showSuccess } from "@/shared/lib/notifications";
import { Bookmark, Loader2 } from "lucide-react";

interface SaveButtonProps {
  saved?: boolean;
  id: string;
}

// SaveButton: the bookmark toggle. Renders the backend-reported saved state and
// delegates the mutation to the server action, which revalidates the listings
// routes so the server components re-render with the fresh is_saved value. No
// local state — the `saved` prop is the single source of truth.
export function SaveButton({ saved = false, id }: SaveButtonProps) {
  const [isPending, startTransition] = useTransition();

  function handleToggle() {
    if (isPending) return;

    const action = saved ? unsaveTrainingAction : saveTrainingAction;

    startTransition(async () => {
      const result = await action(id);
      if (result?.error) {
        showError(result.error || "Failed to save training");
        return;
      }
      showSuccess(result.message || "Training saved successfully");
    });
  }

  return (
    <button
      type="button"
      onClick={handleToggle}
      disabled={isPending}
      className={`cursor-pointer rounded-md p-1.5 transition-colors disabled:opacity-50 ${
        saved
          ? "text-secondary hover:bg-secondary-tint"
          : "text-muted-foreground hover:bg-primary-tint hover:text-primary"
      }`}
    >
      {isPending ? (
        <Loader2 className="size-5 animate-spin" />
      ) : (
        <Bookmark className={`size-5 ${saved ? "fill-current" : ""}`} />
      )}
    </button>
  );
}