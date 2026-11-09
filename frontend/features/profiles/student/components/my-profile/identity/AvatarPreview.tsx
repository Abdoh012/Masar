"use client";

import type { RefObject } from "react";
import { Camera, Loader2 } from "lucide-react";

import { IDENTITY_LABELS, PROFILE_IMAGE_ACCEPT } from "./constants";

interface AvatarPreviewProps {
  name: string | null;
  initials: string;
  previewUrl?: string;
  isPending: boolean;
  inputRef: RefObject<HTMLInputElement | null>;
  onSelect: (file: File) => void;
}

// AvatarPreview: the circular photo — the image or the initials fallback, the
// hover/focus camera overlay, the upload spinner, and the visually-hidden file
// input the whole circle is a <label> for. Display-only beyond forwarding the
// picked file: validation, the upload and removal live in AvatarEditor.
export function AvatarPreview({
  name,
  initials,
  previewUrl,
  isPending,
  inputRef,
  onSelect,
}: AvatarPreviewProps) {
  return (
    <label className="group block cursor-pointer rounded-full bg-secondary-tint p-1 ring-1 ring-secondary/40 transition-shadow focus-within:ring-2 focus-within:ring-ring">
      <span className="relative block size-24 overflow-hidden rounded-full sm:size-28">
        {previewUrl ? (
          <img
            src={previewUrl}
            alt={
              name ? `${name}'s profile photo` : IDENTITY_LABELS.profilePhotoAlt
            }
            className="size-full object-cover"
          />
        ) : (
          <span className="grid size-full place-items-center rounded-full bg-primary text-xl font-semibold text-primary-foreground sm:text-2xl">
            {initials}
          </span>
        )}

        <span className="absolute inset-0 grid place-items-center rounded-full bg-primary/75 text-primary-foreground opacity-0 transition-opacity duration-200 group-hover:opacity-100 group-focus-within:opacity-100">
          <Camera className="size-5" />
          <span className="sr-only">
            {previewUrl
              ? IDENTITY_LABELS.changePhoto
              : IDENTITY_LABELS.addPhoto}
          </span>
        </span>

        {isPending ? (
          <span className="absolute inset-0 grid place-items-center rounded-full bg-primary/70 text-primary-foreground">
            <Loader2 className="size-6 animate-spin" />
            <span className="sr-only">{IDENTITY_LABELS.uploading}</span>
          </span>
        ) : null}

        <input
          ref={inputRef}
          type="file"
          accept={PROFILE_IMAGE_ACCEPT}
          disabled={isPending}
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
      </span>
    </label>
  );
}
