"use client";

import { X } from "lucide-react";

import { SKILLS_LABELS } from "./constants";

interface SkillChipProps {
  skill: string;
  skillId: number;
  onRemove: (id: number) => void;
  disabled?: boolean;
}

// SkillChip: one selected skill, with a remove control. The whole chip is
// tinted with the primary pair so a row of them reads as a set; the × is a real
// button (not a click handler on the chip) so it stays keyboard reachable and
// announces the skill it removes. `skillId` is the taxonomy id sent to the
// backend; `skill` is the display name.
export function SkillChip({ skill, skillId, onRemove, disabled }: SkillChipProps) {
  return (
    <li className="inline-flex items-center gap-1 rounded-full bg-primary-tint py-1 pl-2.5 pr-1 text-xs font-medium text-primary-text">
      {skill}

      <button
        type="button"
        disabled={disabled}
        onClick={() => onRemove(skillId)}
        className="grid size-4 cursor-pointer place-items-center rounded-full text-primary-text/60 transition-colors hover:bg-primary/15 hover:text-primary-text focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
        aria-label={SKILLS_LABELS.remove(skill)}
      >
        <X className="size-3" />
      </button>
    </li>
  );
}


