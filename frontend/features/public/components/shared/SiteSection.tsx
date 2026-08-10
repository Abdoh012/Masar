import type { LegalSection } from "@/features/public/types";
import SectionCard from "@/features/public/components/shared/SectionCard";

interface SiteSectionProps {
  sections: LegalSection[];
}

export function SiteSection({ sections }: SiteSectionProps) {
  return (
    <div className="mx-auto w-full max-w-prose space-y-10">
      {sections.map((section) => (
        <SectionCard key={section.id} {...section} />
      ))}
    </div>
  );
}
