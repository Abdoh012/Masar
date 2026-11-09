"use client";

import { useEffect, useRef, useState, useTransition } from "react";

import { showError, showSuccess } from "@/shared/lib/notifications";

import { removeProfilePhoto, uploadProfilePhoto } from "../../../actions";
import { useFilePreview } from "../../../hooks/useFilePreview";
import { AvatarActions } from "./AvatarActions";
import { AvatarPreview } from "./AvatarPreview";
import {
  IDENTITY_LABELS,
  PROFILE_IMAGE_MAX_BYTES,
  PROFILE_IMAGE_MIME_TYPES,
} from "./constants";

interface AvatarEditorProps {
  name: string | null;
  initials: string;
  src?: string;
}

export function AvatarEditor({ name, initials, src }: AvatarEditorProps) {
  const { file, select, clear } = useFilePreview();
  const [displayUrl, setDisplayUrl] = useState<string | undefined>(src);
  const [isPending, startTransition] = useTransition();
  const inputRef = useRef<HTMLInputElement>(null);

  // The refreshed server value wins whenever it changes (upload reports a new
  // signed URL, removal reports none). Resetting after an upload also stops a
  // revoked object URL from outliving the write.
  useEffect(() => {
    setDisplayUrl(src);
  }, [src]);

  const previewUrl = file?.previewUrl ?? displayUrl;
  // Reflect a stored photo, not the transient local preview: Remove should not
  // appear the instant a first upload starts.
  const hasPhoto = Boolean(displayUrl);

  const openPicker = () => inputRef.current?.click();

  function handleSelect(picked: File) {
    if (!PROFILE_IMAGE_MIME_TYPES.some((mime) => mime === picked.type)) {
      showError(IDENTITY_LABELS.invalidType);
      return;
    }

    if (picked.size > PROFILE_IMAGE_MAX_BYTES) {
      showError(IDENTITY_LABELS.tooLarge);
      return;
    }

    select(picked);

    startTransition(async () => {
      const result = await uploadProfilePhoto(picked);

      if (result.success) {
        setDisplayUrl(result.data?.profileImageUrl ?? undefined);
        clear();
        showSuccess(result.message ?? IDENTITY_LABELS.uploaded);
        return;
      }

      clear();
      showError(result.error ?? IDENTITY_LABELS.uploadFailed);
    });
  }

  function handleRemove() {
    startTransition(async () => {
      const result = await removeProfilePhoto();

      if (result.success) {
        setDisplayUrl(undefined);
        clear();
        showSuccess(result.message ?? IDENTITY_LABELS.removed);
        return;
      }

      showError(result.error ?? IDENTITY_LABELS.removeFailed);
    });
  }

  return (
    <div className="flex shrink-0 flex-col items-center gap-2.5">
      <AvatarPreview
        name={name}
        initials={initials}
        previewUrl={previewUrl}
        isPending={isPending}
        inputRef={inputRef}
        onSelect={handleSelect}
      />

      <AvatarActions
        hasPhoto={hasPhoto}
        isPending={isPending}
        onChangePhoto={openPicker}
        onRemove={handleRemove}
      />
    </div>
  );
}
