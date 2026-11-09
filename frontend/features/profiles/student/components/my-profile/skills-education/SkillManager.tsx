"use client";

import { startTransition } from "react";

import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/shared/components/ui/select";
import { useFormFeedBack } from "@/shared/hooks/useFormFeedback";

import { addStudentSkill, removeStudentSkill } from "../../../actions";
import type { Skill } from "../../../types";
import { SKILLS_LABELS } from "./constants";
import { SkillChip } from "./SkillChip";

interface SkillManagerProps {
  skills: string[];
  skillOptions: Skill[];
}

export function SkillManager({ skills, skillOptions }: SkillManagerProps) {
  const { formAction: addAction, isPending: addPending } = useFormFeedBack(
    addStudentSkill,
    null,
  );
  const { formAction: removeAction, isPending: removePending } =
    useFormFeedBack(removeStudentSkill, null);

  const isPending = addPending || removePending;

  const skillByName = new Map<string, number>(
    skillOptions.map((option) => [option.name, option.id]),
  );

  const addedNames = new Set(skills);
  const available = skillOptions.filter(
    (option) => !addedNames.has(option.name),
  );

  // A `useActionState` dispatch called manually (not via <form action>) must run inside a transition, or `isPending` never updates.
  const submit = (action: (fd: FormData) => void, id: number) => {
    const formData = new FormData();
    formData.set("skill", String(id));
    startTransition(() => action(formData));
  };

  const handleAdd = (name: string) => {
    const id = skillByName.get(name);
    if (id != null) submit(addAction, id);
  };

  const handleRemove = (id: number) => submit(removeAction, id);

  return (
    <div className="space-y-3">
      {skills.length > 0 ? (
        <ul className="flex flex-wrap gap-1.5">
          {skills.map((skill) => {
            const id = skillByName.get(skill) ?? 0;
            return (
              <SkillChip
                key={skill}
                skill={skill}
                skillId={id}
                onRemove={handleRemove}
                disabled={isPending}
              />
            );
          })}
        </ul>
      ) : (
        <p className="text-sm text-muted-foreground">{SKILLS_LABELS.empty}</p>
      )}

      {available.length > 0 ? (
        <Select value="" onValueChange={handleAdd} disabled={isPending}>
          <SelectTrigger
            aria-label={SKILLS_LABELS.selectLabel}
            className="cursor-pointer font-medium"
          >
            <SelectValue placeholder={SKILLS_LABELS.selectPlaceholder} />
          </SelectTrigger>

          <SelectContent>
            {available.map((option) => (
              <SelectItem
                key={option.id}
                value={option.name}
                className="cursor-pointer"
              >
                {option.name}
              </SelectItem>
            ))}
          </SelectContent>
        </Select>
      ) : (
        <p className="text-sm text-muted-foreground">
          {SKILLS_LABELS.allAdded}
        </p>
      )}
    </div>
  );
}
