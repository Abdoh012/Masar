"use client";

import { Bookmark } from "lucide-react";

interface SaveButtonProps {
  saved: boolean;
  disabled?: boolean;
  onToggle: () => void;
}

export function SaveButton({ saved, disabled, onToggle }: SaveButtonProps) {
  return (
    <button
      type="button"
      onClick={(e) => {
        e.preventDefault();
        onToggle();
      }}
      disabled={disabled}
      className={`cursor-pointer rounded-md p-1.5 transition-colors disabled:opacity-50 ${
        saved
          ? "text-secondary hover:bg-secondary-tint"
          : "text-muted-foreground hover:bg-primary-tint hover:text-primary"
      }`}
    >
      <Bookmark className={`size-5 ${saved ? "fill-current" : ""}`} />
    </button>
  );
}
