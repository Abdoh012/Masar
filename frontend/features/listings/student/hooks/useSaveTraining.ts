"use client";

import { useState, useTransition } from "react";

import { saveTraining, unsaveTraining } from "../api";

export function useSaveTraining() {
  const [isPending, startTransition] = useTransition();
  const [error, setError] = useState<string | null>(null);

  function toggle(
    id: string,
    currentlySaved: boolean,
    onToggle: (nextSaved: boolean) => void,
  ) {
    setError(null);
    startTransition(async () => {
      try {
        if (currentlySaved) {
          await unsaveTraining(id);
        } else {
          await saveTraining(id);
        }
        onToggle(!currentlySaved);
      } catch (err) {
        setError(
          err instanceof Error ? err.message : "Failed to update save state",
        );
      }
    });
  }

  return { isPending, error, toggle };
}
