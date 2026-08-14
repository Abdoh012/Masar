"use client";

import { useRef } from "react";

import { Checkbox } from "@/shared/components/ui/checkbox";
import { Label } from "@/shared/components/ui/label";

import { HIRE_INTENT_LABEL } from "./constants";

interface HireIntentToggleProps {
  defaultChecked?: boolean;
}

// Client-bound presentational leaf. The radix Checkbox renders a <button>,
// which FormData ignores, so the checked state is mirrored into a hidden
// `hireIntent` input — the orchestrator reads it off FormData by name on
// submit (structure rules §10 / R-7). No visual state is owned here;
// defaultChecked prefills edit mode.

export function HireIntentToggle({ defaultChecked = false }: HireIntentToggleProps) {
  const hireIntentInputRef = useRef<HTMLInputElement>(null);

  function handleCheckedChange(checked: boolean | "indeterminate") {
    if (hireIntentInputRef.current) {
      hireIntentInputRef.current.value = checked === true ? "true" : "";
    }
  }

  return (
    <div className="space-y-2">
      <Label htmlFor="hireIntent" className="flex items-center gap-2">
        <Checkbox id="hireIntent" defaultChecked={defaultChecked} onCheckedChange={handleCheckedChange} />
        <span>{HIRE_INTENT_LABEL}</span>
      </Label>
      <input
        ref={hireIntentInputRef}
        type="hidden"
        name="hireIntent"
        defaultValue={defaultChecked ? "true" : ""}
      />
    </div>
  );
}
