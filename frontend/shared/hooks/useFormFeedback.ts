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
    if (!state) return;

    // Skip the untouched initial state (an ActionState with nothing to report)
    // so no toast fires on first mount — only after a real submission.
    const hasFeedback =
      Boolean(state.success) ||
      Boolean(state.message) ||
      Boolean(state.error) ||
      (state.fieldErrors != null && Object.keys(state.fieldErrors).length > 0);
    if (!hasFeedback) return;

    if (!state.success) {
      if (state.fieldErrors) {
        for (const errors of Object.values(state.fieldErrors)) {
          // The backend reports field errors as either a single message string
          // ({ field: "message" }) or a list ({ field: ["message", ...] }).
          // Normalize both into a string[] so a lone string isn't iterated
          // character-by-character (which produced one-letter toasts).
          const list = Array.isArray(errors) ? errors : [errors];
          for (const error of list) {
            showError(error || "Something went wrong!");
          }
        }
        return;
      }

      showError(state.message || state.error || "Something went wrong!");
    } else {
      showSuccess(state.message || "Operation completed successfully!");
    }
  }, [state]);

  return { formAction, state, isPending };
}
