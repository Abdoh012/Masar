import { notFound } from "next/navigation";

import { Sparkles } from "lucide-react";

import { fetchStudentSkills, fetchSkillOptions } from "../../../api";
import {
  normalizeSkillOptions,
  normalizeStudentSkills,
} from "../../../lib/normalize";
import { ProfileSection } from "../ProfileSection";
import { ProfileSubPanel } from "../ProfileSubPanel";
import { SkillManager } from "./SkillManager";
import EducationList from "./EducationList";
import { SECTION_LABELS, SKILLS_LABELS } from "./constants";
import type { Skill } from "../../../types";

export default async function SkillsEducationContainer() {
  const [skillsResult, optionsResult] = await Promise.allSettled([
    fetchStudentSkills(),
    fetchSkillOptions(),
  ]);

  let skills: string[] = [];
  let skillOptions: Skill[] = [];

  if (skillsResult.status === "fulfilled") {
    const sr = skillsResult.value;
    if (sr.status === 404) {
      notFound();
    }
    if (sr.success) {
      skills = normalizeStudentSkills(sr.data);
    }
  }

  if (optionsResult.status === "fulfilled") {
    const or = optionsResult.value;
    if (or.success) {
      skillOptions = normalizeSkillOptions(or.data);
    }
  }

  return (
    <ProfileSection
      icon={<Sparkles className="size-4" />}
      title={SECTION_LABELS.title}
      description={SECTION_LABELS.description}
    >
      <div className="space-y-4">
        <ProfileSubPanel
          icon={<Sparkles className="size-4" />}
          title={SKILLS_LABELS.title}
          description={SKILLS_LABELS.description(skillOptions.length)}
        >
          <SkillManager skills={skills} skillOptions={skillOptions} />
        </ProfileSubPanel>

        <EducationList />
      </div>
    </ProfileSection>
  );
}


