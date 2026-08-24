import { Check } from "lucide-react";

import { cn } from "@/shared/lib/utils";

import { SKILL_OPTIONS, TRAINING_APPLICATION_FIELDS } from "./constants";

interface SkillChipsProps {
  selected: string[];
  onToggle: (skill: string) => void;
}

// SkillChips: the step-3 optional skills multi-select. One toggleable chip per
// skill in the catalog; selected chips flip to the solid primary treatment with
// a check. Controlled by the orchestrator so the selection survives navigation.
// Pure leaf — no fetching, no state.
export function SkillChips({ selected, onToggle }: SkillChipsProps) {
  return (
    <div className="space-y-2">
      <div className="flex items-center justify-between gap-2">
        <span className="text-sm font-medium text-foreground">
          {TRAINING_APPLICATION_FIELDS.skills.label}
        </span>
        <span className="text-xs text-muted-foreground">(optional)</span>
      </div>

      <ul className="flex flex-wrap gap-2">
        {SKILL_OPTIONS.map((skill) => {
          const isSelected = selected.includes(skill);

          return (
            <li key={skill}>
              <button
                type="button"
                aria-pressed={isSelected}
                onClick={() => onToggle(skill)}
                className={cn(
                  "inline-flex cursor-pointer items-center gap-1.5 rounded-full border px-3 py-1.5 text-sm transition-colors",
                  isSelected
                    ? "border-primary bg-primary text-primary-foreground"
                    : "border-input bg-card text-foreground hover:border-primary/60",
                )}
              >
                {isSelected ? <Check className="size-3.5" /> : null}
                {skill}
              </button>
            </li>
          );
        })}
      </ul>
    </div>
  );
}