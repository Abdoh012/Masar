import Motion from "@/shared/components/animation/Motion";
import { fadeInUp } from "@/shared/lib/animations";
import { cn } from "@/shared/lib/utils";

import { GROUP_ICONS, type CertificateGroupConfig } from "./CertificateGroup";

// CertificateGroupEmpty: dashed "nothing here yet" panel for one certificate
// group, shown when that bucket has no records. Renders the group's tinted
// icon and its own copy. Leaf — display only.
export function CertificateGroupEmpty({ config }: { config: CertificateGroupConfig }) {
  const Icon = GROUP_ICONS[config.icon];

  return (
    <Motion
      variants={fadeInUp}
      initial="hidden"
      whileInView="visible"
      viewport={{ once: true }}
      className="flex flex-col items-center gap-2 rounded-xl border border-dashed border-border px-6 py-8 text-center"
    >
      <span className={cn("flex size-11 items-center justify-center rounded-full", config.accent)}>
        <Icon className="size-5" />
      </span>
      <p className="font-sans text-base font-semibold text-foreground">{config.emptyTitle}</p>
      <p className="max-w-sm text-sm text-muted-foreground">{config.emptyMessage}</p>
    </Motion>
  );
}