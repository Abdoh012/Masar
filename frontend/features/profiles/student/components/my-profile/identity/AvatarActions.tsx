"use client";

import { Camera, Trash2 } from "lucide-react";

import { Button } from "@/shared/components/ui/button";

import { IDENTITY_LABELS } from "./constants";

interface AvatarActionsProps {
  hasPhoto: boolean;
  isPending: boolean;
  onChangePhoto: () => void;
  onRemove: () => void;
}

// AvatarActions: the visible text controls under the circle. The camera overlay
// alone is invisible on touch devices and easy to miss with a mouse, so the
// same picker is also offered as a button; Remove only exists once there is a
// photo to remove.
export function AvatarActions({
  hasPhoto,
  isPending,
  onChangePhoto,
  onRemove,
}: AvatarActionsProps) {
  return (
    <div className="flex flex-wrap items-center justify-center gap-1">
      <Button
        type="button"
        variant="ghost"
        size="sm"
        className="text-muted-foreground"
        onClick={onChangePhoto}
        disabled={isPending}
      >
        <Camera />
        {hasPhoto
          ? IDENTITY_LABELS.changePhotoAction
          : IDENTITY_LABELS.addPhotoAction}
      </Button>

      {hasPhoto ? (
        <Button
          type="button"
          variant="ghost"
          size="sm"
          className="text-muted-foreground hover:bg-error-bg hover:text-error-fg"
          onClick={onRemove}
          disabled={isPending}
        >
          <Trash2 />
          {IDENTITY_LABELS.removePhoto}
        </Button>
      ) : null}
    </div>
  );
}
