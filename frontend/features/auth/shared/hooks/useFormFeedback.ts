import { showSuccess, showError } from "@/shared/lib/notifications";
import { useActionState, useEffect } from "react";
import { ActionState } from "@/types/server-action";

export function useFormFeedBack(
  action: (
    prevData: ActionState | null,
    formData: FormData,
  ) => Promise<ActionState | undefined>,
  initialState: ActionState | null,
) {
  const [state, formAction, isPending] = useActionState(
    (prevState: ActionState | null | undefined, formData: FormData) =>
      action(prevState ?? null, formData),
    initialState,
  );

  // Handle form feedback
  useEffect(() => {
    // Skip the untouched initial state (null or an empty ActionState) so
    // no toast fires on first mount — only after a real submission.
    if (!state || (!state.success && !state.message && !state.error)) return;

    if (!state?.success) {
      if (state.fieldErrors) {
        for (const key in state.fieldErrors) {
          showError(state.fieldErrors[key]?.[0] || "Something went wrong!");
        }
        return;
      }

      showError(state.message || state.error || "Something went wrong!");
    } else if (state?.success) {
      showSuccess(state.message || "Operation completed successfully!");
    }
  }, [state]);

  return { formAction, state, isPending };
}
