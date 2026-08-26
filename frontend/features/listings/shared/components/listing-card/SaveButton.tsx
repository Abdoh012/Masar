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
      aria-label={saved ? "Remove from saved" : "Save training"}
      className="cursor-pointer rounded-md p-1.5 text-muted-foreground transition-colors hover:bg-primary-tint hover:text-primary disabled:opacity-50"
    >
      <Bookmark
        className={`size-5 ${saved ? "fill-current" : ""}`}
      />
    </button>
  );
}
