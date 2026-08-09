"use client";

import { useEffect, useRef, type ReactNode } from "react";
import { X } from "lucide-react";

import { Button } from "@/shared/components/ui/button";
import Motion from "@/shared/components/animation/Motion";

import { SIDEBAR_IDS, SIDEBAR_LABELS } from "../constants";

interface SidebarDrawerProps {
  open: boolean;
  onClose: () => void;
  children: ReactNode;
}

// SidebarDrawer: mobile (<lg) navigation drawer showing the same server-
// rendered content as the desktop rail (forwarded as children, R-8). Closes
// on backdrop tap, the visible close button, Escape, or selecting a nav item
// (FR-017-FR-019, R-6). Focus moves into the drawer on open and returns to
// the trigger on close (FR-019).
export function SidebarDrawer({ open, onClose, children }: SidebarDrawerProps) {
  const closeRef = useRef<HTMLButtonElement>(null);
  const triggerRef = useRef<Element | null>(null);

  useEffect(() => {
    if (!open) return;

    triggerRef.current = document.activeElement;
    closeRef.current?.focus();

    const onKey = (e: KeyboardEvent) => {
      if (e.key === "Escape") onClose();
    };
    document.addEventListener("keydown", onKey);

    const prevOverflow = document.body.style.overflow;
    document.body.style.overflow = "hidden";

    return () => {
      document.removeEventListener("keydown", onKey);
      document.body.style.overflow = prevOverflow;
      (triggerRef.current as HTMLElement | null)?.focus();
    };
  }, [open, onClose]);

  if (!open) return null;

  return (
    <div className="fixed inset-0 z-40 lg:hidden">
      <button
        type="button"
        aria-label={SIDEBAR_LABELS.closeDrawer}
        onClick={onClose}
        className="absolute inset-0 h-full w-full bg-primary-950/50"
      />
      <Motion
        as="div"
        role="dialog"
        aria-modal="true"
        aria-labelledby={SIDEBAR_IDS.drawerTitle}
        initial={{ x: "-100%" }}
        animate={{ x: 0 }}
        transition={{ duration: 0.2, ease: "easeOut" }}
        className="absolute inset-y-0 left-0 flex w-72 max-w-[85vw] flex-col border-r border-border bg-background shadow-card sm:w-80"
      >
        <header className="flex h-12 shrink-0 items-center justify-end border-b border-border px-2">
          <h2 id={SIDEBAR_IDS.drawerTitle} className="sr-only">
            {SIDEBAR_LABELS.drawerTitle}
          </h2>
          <Button
            type="button"
            variant="ghost"
            size="icon"
            ref={closeRef}
            aria-label={SIDEBAR_LABELS.closeDrawer}
            onClick={onClose}
          >
            <X strokeWidth={2} aria-hidden="true" />
          </Button>
        </header>
        <div
          className="flex min-h-0 flex-1 flex-col"
          onClickCapture={(e) => {
            if ((e.target as HTMLElement).closest("a")) onClose();
          }}
        >
          {children}
        </div>
      </Motion>
    </div>
  );
}