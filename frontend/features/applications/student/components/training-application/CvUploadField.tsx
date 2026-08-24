"use client";

import { useRef } from "react";

import { FileText, Upload, X } from "lucide-react";

import { Button } from "@/shared/components/ui/button";
import { Label } from "@/shared/components/ui/label";

import type { CvFileState } from "../../types";
import { CV_FIELD_LABELS, formatFileSize } from "./constants";

interface CvUploadFieldProps {
  file: CvFileState | null;
  onSelect: (file: File) => void;
  onRemove: () => void;
}

// CvUploadField: the step-1 CV upload control. UI-only — no file is uploaded;
// selecting a file just reports its name/size up to the orchestrator for
// display, and the selected state offers Remove (and re-open the picker via the
// dropzone's "click to upload"). "use client": owns a ref to the hidden file
// input so the dropzone button can open the picker.
export function CvUploadField({ file, onSelect, onRemove }: CvUploadFieldProps) {
  const inputRef = useRef<HTMLInputElement>(null);

  return (
    <div className="space-y-1.5">
      <div className="flex items-center justify-between gap-2">
        <Label>{CV_FIELD_LABELS.label}</Label>
        <span className="text-xs text-muted-foreground">(optional)</span>
      </div>

      <input
        ref={inputRef}
        type="file"
        accept=".pdf,.doc,.docx"
        className="sr-only"
        onChange={(event) => {
          const selected = event.target.files?.[0];
          if (selected) onSelect(selected);
          event.target.value = "";
        }}
      />

      {file ? (
        <div className="flex flex-col gap-3 rounded-lg border border-border bg-card p-4 sm:flex-row sm:items-center sm:justify-between">
          <div className="flex min-w-0 items-center gap-3">
            <span className="grid size-10 shrink-0 place-items-center rounded-md bg-primary-tint text-primary">
              <FileText className="size-5" />
            </span>
            <div className="min-w-0">
              <p className="truncate text-sm font-medium text-foreground">{file.name}</p>
              <p className="text-xs text-muted-foreground">{formatFileSize(file.size)}</p>
            </div>
          </div>

          <Button type="button" variant="outline" size="sm" onClick={onRemove}>
            <X className="size-4" />
            {CV_FIELD_LABELS.remove}
          </Button>
        </div>
      ) : (
        <button
          type="button"
          onClick={() => inputRef.current?.click()}
          className="flex w-full cursor-pointer flex-col items-center gap-2 rounded-lg border border-dashed border-border bg-card p-6 text-center transition-colors hover:border-primary/50 hover:bg-primary-tint/40 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
        >
          <span className="grid size-10 place-items-center rounded-full bg-primary-tint text-primary">
            <Upload className="size-5" />
          </span>
          <span className="text-sm font-medium text-foreground">{CV_FIELD_LABELS.empty}</span>
          <span className="text-xs text-muted-foreground">{CV_FIELD_LABELS.hint}</span>
        </button>
      )}
    </div>
  );
}