"use client";

import { useState, useTransition } from "react";

import { saveTrainingAction, unsaveTrainingAction } from "../actions";

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
      const action = currentlySaved ? unsaveTrainingAction : saveTrainingAction;
      const result = await action(id);

      if (result.error) {
        setError(result.error);
        return;
      }

      onToggle(!currentlySaved);
    });
  }

  return { isPending, error, toggle };
}
