import { FileText, Upload, X } from "lucide-react";

import { Button } from "@/shared/components/ui/button";
import { Label } from "@/shared/components/ui/label";

import type { CvFileState } from "../../types";
import { CV_FIELD_LABELS, formatFileSize } from "./constants";

interface CvUploadFieldProps {
  file: CvFileState | null;
  error?: string | null;
  onOpenPicker: () => void;
  onRemove: () => void;
}

// CvUploadField: the step-1 CV control. Purely presentational — the sr-only
// <input name="cv"> it used to own now lives in the always-mounted CvFileInput
// carrier (so the file survives step changes), and CV presence is gated by the
// orchestrator (native bubbles can't anchor to a sr-only input), which passes
// an inline error here to render under the dropzone. This leaf only shows the
// dropzone (opening the carrier's picker), the selected-file card with Remove,
// and that inline error.
export function CvUploadField({
  file,
  error,
  onOpenPicker,
  onRemove,
}: CvUploadFieldProps) {
  return (
    <div className="space-y-1.5">
      <div className="flex items-center justify-between gap-2">
        <Label>{CV_FIELD_LABELS.label}</Label>
      </div>

      {file ? (
        <div className="flex flex-col gap-3 rounded-lg border border-border bg-card p-4 sm:flex-row sm:items-center sm:justify-between">
          <div className="flex min-w-0 items-center gap-3">
            <span className="grid size-10 shrink-0 place-items-center rounded-md bg-primary-tint text-primary">
              <FileText className="size-5" />
            </span>

            <div className="min-w-0">
              <p className="truncate text-sm font-medium text-foreground">
                {file.name}
              </p>

              <p className="text-xs text-muted-foreground">
                {formatFileSize(file.size)}
              </p>
            </div>
          </div>

          <Button
            type="button"
            variant="outline"
            size="sm"
            className="cursor-pointer"
            onClick={onRemove}
          >
            <X className="size-4" />
            {CV_FIELD_LABELS.remove}
          </Button>
        </div>
      ) : (
        <button
          type="button"
          onClick={onOpenPicker}
          className="flex w-full cursor-pointer flex-col items-center gap-2 rounded-lg border border-dashed border-border bg-card p-6 text-center transition-colors hover:border-primary/50 hover:bg-primary-tint/40 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
        >
          <span className="grid size-10 place-items-center rounded-full bg-primary-tint text-primary">
            <Upload className="size-5" />
          </span>
          <span className="text-sm font-medium text-foreground">
            {CV_FIELD_LABELS.empty}
          </span>
          <span className="text-xs text-muted-foreground">
            {CV_FIELD_LABELS.hint}
          </span>
        </button>
      )}

      {error ? (
        <p className="text-xs text-destructive">{error}</p>
      ) : null}
    </div>
  );
}