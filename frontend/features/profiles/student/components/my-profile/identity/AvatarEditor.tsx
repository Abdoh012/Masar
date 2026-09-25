"use client";

import { Camera } from "lucide-react";

import { useFilePreview } from "../../../hooks/useFilePreview";

import { IDENTITY_LABELS } from "./constants";

interface AvatarEditorProps {
  name: string;
  initials: string;
  src?: string;
}

// AvatarEditor: the identity card's photo. The whole circle is a <label> over a
// visually-hidden file input, so clicking or keyboard-focusing it opens the
// picker, and a camera overlay fades in on hover/focus. The picked image is
// previewed from a local object URL — no upload yet, so the file only exists in
// the tab. Falls back to the initials avatar until a photo exists.
export function AvatarEditor({ name, initials, src }: AvatarEditorProps) {
  const { file, select } = useFilePreview();
  const preview = file?.previewUrl ?? src;

  return (
    <span className="group relative shrink-0">
      <label className="block cursor-pointer rounded-full bg-secondary-tint p-1 ring-1 ring-secondary/40 transition-shadow focus-within:ring-2 focus-within:ring-ring">
        <span className="relative block size-24 overflow-hidden rounded-full sm:size-28">
          {preview ? (
            // Local object URL (or a future CDN path) — next/image can't optimize
            // a blob, and the "fill" layout would need a positioned parent.
            // eslint-disable-next-line @next/next/no-img-element
            <img
              src={preview}
              alt={`${name}'s profile photo`}
              className="size-full object-cover"
            />
          ) : (
            <span className="grid size-full place-items-center rounded-full bg-primary text-xl font-semibold text-primary-foreground sm:text-2xl">
              {initials}
            </span>
          )}

          {/* Inset by the gold padding ring so the overlay hugs the photo. */}
          <span className="absolute inset-0 grid place-items-center rounded-full bg-primary/75 text-primary-foreground opacity-0 transition-opacity duration-200 group-hover:opacity-100 group-focus-within:opacity-100">
            <Camera className="size-5" />
            <span className="sr-only">{IDENTITY_LABELS.changePhoto}</span>
          </span>
        </span>

        <input
          type="file"
          accept="image/*"
          className="sr-only"
          onChange={(event) => {
            const picked = event.target.files?.[0];

            if (picked) {
              select(picked);
            }

            // Reset so re-picking the same file fires another change event.
            event.target.value = "";
          }}
        />
      </label>
    </span>
  );
}
