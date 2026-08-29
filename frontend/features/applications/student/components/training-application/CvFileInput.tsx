"use client";

import { forwardRef } from "react";

interface CvFileInputProps {
  onSelect: (file: File) => void;
}

// CvFileInput: the form-level carrier for the CV. Rendered once inside the
// wizard's <form> (beside the hidden payload fields), it never unmounts, so its
// selected file persists across step changes and is picked up by the step-3
// FormData natively via `name="cv"`. CV presence is NOT native-`required` here:
// the input is sr-only, so a native validation bubble would anchor to the top
// of the form instead of the visible dropzone — the orchestrator gates it
// manually and shows an inline error under the step-1 CV field. Its value is
// only ever cleared by the Remove handler in the orchestrator (never here).
// "use client": forwardRef + native file input.
export const CvFileInput = forwardRef<HTMLInputElement, CvFileInputProps>(
  function CvFileInput({ onSelect }, ref) {
    return (
      <input
        ref={ref}
        type="file"
        name="cv"
        accept=".pdf,.doc,.docx"
        className="sr-only"
        onChange={(event) => {
          const selected = event.target.files?.[0];
          if (selected) onSelect(selected);
        }}
      />
    );
  },
);