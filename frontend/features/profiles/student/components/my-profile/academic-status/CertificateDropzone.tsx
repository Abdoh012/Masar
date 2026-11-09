"use client";

import { useRef, useState } from "react";
import { FileText, Upload, X } from "lucide-react";

import { Button } from "@/shared/components/ui/button";
import { cn } from "@/shared/lib/utils";

import { formatFileSize } from "../../../lib/format";
import { CERTIFICATE_UPLOAD_LABELS } from "./constants";
import type { AcademicCertificate } from "../../../types";
import type { SelectedFile } from "../../../types";

const CERTIFICATE_ACCEPT = "application/pdf,image/png,image/jpeg,image/webp";

interface CertificateDropzoneProps {
  /** The saved certificate from GET /students/me/academic-status, if any. */
  serverCert: AcademicCertificate | null;
  /** A file picked locally that hasn't been saved yet. */
  localPick: SelectedFile | null;
  /** Ref of the save form's real `<input type="file">` (its File goes into FormData). */
  fileInputRef: React.RefObject<HTMLInputElement | null> | null;
  onLocalSelect: (file: File) => void;
  onLocalClear: () => void;
  /** Removes the server-stored certificate via its own form action. */
  onRemoveServerCert?: () => void;
}

const CERT_TILE_CLASS =
  "flex items-center gap-3 rounded-xl border border-border bg-card p-3";

// CertificateDropzone: the graduate-only certificate area. Three states:
// 1. serverCert present → cert tile + Remove (hits the server action).
// 2. localPick present → preview tile + Remove (clears the local pick).
// 3. neither → empty dropzone (drop + Browse → clicks fileInputRef so the real
//    file input is the one whose File lands in FormData on submit).
export function CertificateDropzone({
  serverCert,
  localPick,
  fileInputRef,
  onLocalSelect,
  onLocalClear,
  onRemoveServerCert,
}: CertificateDropzoneProps) {
  const [isDragging, setIsDragging] = useState(false);
  const inputRef = useRef<HTMLInputElement>(null);

  const openPicker = () => {
    fileInputRef?.current?.click();
    inputRef.current?.click();
  };

  // A saved certificate takes precedence — show its tile with a Remove button.
  if (serverCert) {
    return (
      <div className="space-y-2">
        <div className={CERT_TILE_CLASS}>
          <span className="grid size-12 shrink-0 place-items-center rounded-lg bg-primary-tint text-primary-text">
            <FileText className="size-5" />
          </span>

          <div className="min-w-0 flex-1">
            <p className="truncate text-sm font-medium text-foreground">
              {serverCert.fileName}
            </p>
            <p className="text-xs text-muted-foreground">
              {CERTIFICATE_UPLOAD_LABELS.uploaded}
            </p>
          </div>

          <Button
            type="button"
            variant="ghost"
            size="icon"
            className="size-8 shrink-0 cursor-pointer text-muted-foreground hover:text-error-fg"
            onClick={onRemoveServerCert}
            aria-label={CERTIFICATE_UPLOAD_LABELS.remove}
          >
            <X className="size-4" />
          </Button>
        </div>
      </div>
    );
  }

  // A locally picked file that hasn't been saved yet — preview + Remove.
  if (localPick) {
    const isImage = localPick.type.startsWith("image/");

    return (
      <div className="space-y-2">
        <div className={CERT_TILE_CLASS}>
          {isImage && localPick.previewUrl ? (
            // Local object URL — next/image can't optimize a blob.
            // eslint-disable-next-line @next/next/no-img-element
            <img
              src={localPick.previewUrl}
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
              {localPick.name}
            </p>
            <p className="text-xs text-muted-foreground">
              {formatFileSize(localPick.size)}
            </p>
          </div>

          <Button
            type="button"
            variant="ghost"
            size="icon"
            className="size-8 shrink-0 cursor-pointer text-muted-foreground hover:text-error-fg"
            onClick={onLocalClear}
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
            onLocalSelect(dropped);
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
              onLocalSelect(picked);
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
