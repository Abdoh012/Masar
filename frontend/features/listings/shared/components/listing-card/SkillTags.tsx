import { CARD_SKILLS_ARIA_LABEL } from "./constants";

interface SkillTagsProps {
  skills: string[];
}

// SkillTags: the small skill pills on the shared ListingCard. Pure leaf.
export function SkillTags({ skills }: SkillTagsProps) {
  return (
    <ul aria-label={CARD_SKILLS_ARIA_LABEL} className="flex flex-wrap gap-1.5">
      {skills.map((skill) => (
        <li
          key={skill}
          className="rounded-md bg-neutral-200 px-2 py-1 text-xs font-medium text-neutral-800"
        >
          {skill}
        </li>
      ))}
    </ul>
  );
}