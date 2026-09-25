import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/shared/components/ui/select";

import { SKILL_CATALOG } from "../constants";
import { SKILLS_LABELS } from "./constants";
import { SkillChip } from "./SkillChip";

interface SkillSelectProps {
  skills: string[];
  onAdd: (skill: string) => void;
  onRemove: (skill: string) => void;
}

// SkillSelect: the skills control. Selected skills render as removable chips
// above a plain dropdown of the catalog options that aren't added yet — picking
// one appends it, and the trigger returns to its placeholder so the next pick
// starts from a clean slate. There's no free-text entry: the catalog is the
// source of truth, so what a reviewer filters on stays consistent across
// candidates. Pure leaf — the list it offers is derived here, the state lives in
// the parent.
//
// The trigger is pinned to value="" on purpose: that's Radix's "nothing
// selected" state, which is what makes the placeholder show and snap back after
// each pick. Empty strings are only illegal as an *item* value, never as the
// trigger's value — so no sentinel token is needed here (unlike the listings
// FilterSelect, whose sentinel has to be a visible, selectable "All" row).
export function SkillSelect({ skills, onAdd, onRemove }: SkillSelectProps) {
  const available = SKILL_CATALOG.filter((skill) => !skills.includes(skill));

  return (
    <div className="space-y-3">
      {skills.length > 0 ? (
        <ul className="flex flex-wrap gap-1.5">
          {skills.map((skill) => (
            <SkillChip key={skill} skill={skill} onRemove={onRemove} />
          ))}
        </ul>
      ) : (
        <p className="text-sm text-muted-foreground">{SKILLS_LABELS.empty}</p>
      )}

      {available.length > 0 ? (
        <Select
          value=""
          onValueChange={(value) => {
            if (value) {
              onAdd(value);
            }
          }}
        >
          <SelectTrigger
            aria-label={SKILLS_LABELS.selectLabel}
            className="cursor-pointer font-medium"
          >
            <SelectValue placeholder={SKILLS_LABELS.selectPlaceholder} />
          </SelectTrigger>

          <SelectContent>
            {available.map((skill) => (
              <SelectItem
                key={skill}
                value={skill}
                className="cursor-pointer"
              >
                {skill}
              </SelectItem>
            ))}
          </SelectContent>
        </Select>
      ) : (
        <p className="text-sm text-muted-foreground">{SKILLS_LABELS.allAdded}</p>
      )}
    </div>
  );
}
