import { Button } from "@/shared/components/ui/button";

import { SUBMIT_LABEL } from "./constants";

interface FormSubmitButtonProps {
  mode: "create" | "edit";
  error?: string | null;
}

// FormSubmitButton: the listing form's dedicated submit control (structure
// rules §17). Renders the validation feedback and the submit button; the
// submission mechanics live in the useListingFormSubmit hook.
export function FormSubmitButton({ mode, error }: FormSubmitButtonProps) {
  return (
    <div className="space-y-4">
      {error ? (
        <p role="alert" className="text-sm text-error-fg">
          {error}
        </p>
      ) : null}

      <Button type="submit" className="w-full sm:w-auto">
        {mode === "edit" ? SUBMIT_LABEL.edit : SUBMIT_LABEL.create}
      </Button>
    </div>
  );
}
