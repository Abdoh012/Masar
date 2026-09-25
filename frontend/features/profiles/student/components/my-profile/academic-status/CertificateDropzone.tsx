"use client";

import { useRef, useState } from "react";
import { FileText, Upload, X } from "lucide-react";

import { Button } from "@/shared/components/ui/button";
import { cn } from "@/shared/lib/utils";

import { formatFileSize } from "../../../lib/format";
import { CERTIFICATE_UPLOAD_LABELS } from "./constants";
import type { SelectedFile } from "../../../types";

const CERTIFICATE_ACCEPT = "application/pdf,image/png,image/jpeg,image/webp";

interface CertificateDropzoneProps {
  file: SelectedFile | null;
  onSelect: (file: File) => void;
  onClear: () => void;
}

// CertificateDropzone: the graduate-only certificate picker. A real drop target
// (dragover / dragleave / drop) plus a Browse button that opens the same hidden
// input. The picked file is previewed locally — an image renders as a
// thumbnail, a PDF as a file tile with its name and size. Nothing is uploaded,
// and the copy under it says so rather than implying a successful upload.
export function CertificateDropzone({
  file,
  onSelect,
  onClear,
}: CertificateDropzoneProps) {
  const [isDragging, setIsDragging] = useState(false);
  const inputRef = useRef<HTMLInputElement>(null);

  const openPicker = () => inputRef.current?.click();

  if (file) {
    const isImage = file.type.startsWith("image/");

    return (
      <div className="space-y-2">
        <div className="flex items-center gap-3 rounded-xl border border-border bg-card p-3">
          {isImage && file.previewUrl ? (
            // Local object URL — next/image can't optimize a blob.
            // eslint-disable-next-line @next/next/no-img-element
            <img
              src={file.previewUrl}
              alt=""
              className="size-12 shrink-0 rounded-lg border border-border object-cover"
            />
          ) : (
            <span className="grid size-12 shrink-0 place-items-center rounded-lg bg-primary-tint text-primary-text">
              <FileText className="size-5" />
            </span>
          )}

          <div className="min-w-0 flex-1">
            <p className="truncate text-sm font-medium text-foreground">
              {file.name}
            </p>
            <p className="text-xs text-muted-foreground">
              {formatFileSize(file.size)}
            </p>
          </div>

          <Button
            type="button"
            variant="ghost"
            size="icon"
            className="size-8 shrink-0 cursor-pointer text-muted-foreground hover:text-error-fg"
            onClick={onClear}
            aria-label={CERTIFICATE_UPLOAD_LABELS.remove}
          >
            <X className="size-4" />
          </Button>
        </div>

        <p className="text-xs text-muted-foreground">
          {CERTIFICATE_UPLOAD_LABELS.localOnly}
        </p>
      </div>
    );
  }

  return (
    <div className="space-y-2">
      <div
        onDragOver={(event) => {
          event.preventDefault();
          setIsDragging(true);
        }}
        onDragLeave={() => setIsDragging(false)}
        onDrop={(event) => {
          event.preventDefault();
          setIsDragging(false);

          const dropped = event.dataTransfer.files?.[0];

          if (dropped) {
            onSelect(dropped);
          }
        }}
        className={cn(
          "flex flex-col items-center gap-2 rounded-xl border border-dashed p-6 text-center transition-colors",
          isDragging
            ? "border-primary bg-primary-tint"
            : "border-border bg-card",
        )}
      >
        <span
          className={cn(
            "grid size-10 place-items-center rounded-full",
            isDragging
              ? "bg-primary text-primary-foreground"
              : "bg-primary-tint text-primary-text",
          )}
        >
          <Upload className="size-5" />
        </span>

        <p className="text-sm font-medium text-foreground">
          {CERTIFICATE_UPLOAD_LABELS.empty}
        </p>
        <p className="text-xs text-muted-foreground">
          {CERTIFICATE_UPLOAD_LABELS.hint}
        </p>

        <Button
          type="button"
          variant="outline"
          size="sm"
          className="mt-1 cursor-pointer"
          onClick={openPicker}
        >
          {CERTIFICATE_UPLOAD_LABELS.browse}
        </Button>

        <input
          ref={inputRef}
          type="file"
          accept={CERTIFICATE_ACCEPT}
          className="sr-only"
          onChange={(event) => {
            const picked = event.target.files?.[0];

            if (picked) {
              onSelect(picked);
            }

            // Reset so re-picking the same file fires another change event.
            event.target.value = "";
          }}
        />
      </div>

      <p className="text-xs text-muted-foreground">
        {CERTIFICATE_UPLOAD_LABELS.localOnly}
      </p>
    </div>
  );
}
