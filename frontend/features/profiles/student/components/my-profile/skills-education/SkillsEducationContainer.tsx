"use client";

import { useState } from "react";
import { GraduationCap, Plus, Sparkles } from "lucide-react";

import { Button } from "@/shared/components/ui/button";

import { createEmptyEducationEntry } from "../../../lib/education";
import { INITIAL_EDUCATION, INITIAL_SKILLS, SKILL_CATALOG } from "../constants";
import { ProfileSection } from "../ProfileSection";
import { ProfileSubPanel } from "../ProfileSubPanel";
import { EducationEntryCard } from "./EducationEntryCard";
import { SkillSelect } from "./SkillSelect";
import { EDUCATION_LABELS, SECTION_LABELS, SKILLS_LABELS } from "./constants";
import type { EducationEntry } from "../../../types";

// SkillsEducationContainer: the two list-editing blocks in one section — the
// skill dropdown and the education entries. Both lists are local state seeded
// from constants: add / remove / edit all work immediately, but nothing is
// persisted yet (no skills or education API). The Add-education and Save actions
// are therefore the seam where a real save lands — see the TODO on the section.
export default function SkillsEducationContainer() {
  const [skills, setSkills] = useState<string[]>(INITIAL_SKILLS);
  const [education, setEducation] = useState<EducationEntry[]>(INITIAL_EDUCATION);

  const addSkill = (skill: string) => {
    setSkills((current) =>
      current.includes(skill) ? current : [...current, skill],
    );
  };

  const removeSkill = (skill: string) => {
    setSkills((current) => current.filter((entry) => entry !== skill));
  };

  const addEducationEntry = () => {
    setEducation((current) => [...current, createEmptyEducationEntry()]);
  };

  const updateEducationEntry = (id: string, patch: Partial<EducationEntry>) => {
    setEducation((current) =>
      current.map((entry) => (entry.id === id ? { ...entry, ...patch } : entry)),
    );
  };

  const removeEducationEntry = (id: string) => {
    setEducation((current) => current.filter((entry) => entry.id !== id));
  };

  return (
    <ProfileSection
      icon={Sparkles}
      title={SECTION_LABELS.title}
      description={SECTION_LABELS.description}
    >
      <div className="grid gap-4 lg:grid-cols-2">
        <ProfileSubPanel
          icon={Sparkles}
          title={SKILLS_LABELS.title}
          description={SKILLS_LABELS.description(SKILL_CATALOG.length)}
        >
          {/* TODO: connect to backend — the updateSkills server action
              (student/actions.ts) replaces this once the skills API exists. */}
          <SkillSelect
            skills={skills}
            onAdd={addSkill}
            onRemove={removeSkill}
          />
        </ProfileSubPanel>

        <ProfileSubPanel
          icon={GraduationCap}
          title={EDUCATION_LABELS.title}
          description={EDUCATION_LABELS.description}
        >
          {/* TODO: connect to backend — the updateEducation server action
              (student/actions.ts) replaces this once the education API exists. */}
          {education.length > 0 ? (
            <ul className="space-y-3">
              {education.map((entry) => (
                <EducationEntryCard
                  key={entry.id}
                  entry={entry}
                  onChange={updateEducationEntry}
                  onRemove={removeEducationEntry}
                />
              ))}
            </ul>
          ) : (
            <p className="text-sm text-muted-foreground">
              {EDUCATION_LABELS.emptyTitle}
            </p>
          )}

          <Button
            type="button"
            variant="outline"
            size="sm"
            className="mt-4 w-full cursor-pointer"
            onClick={addEducationEntry}
          >
            <Plus className="size-4" />
            {EDUCATION_LABELS.add}
          </Button>
        </ProfileSubPanel>
      </div>
    </ProfileSection>
  );
}
