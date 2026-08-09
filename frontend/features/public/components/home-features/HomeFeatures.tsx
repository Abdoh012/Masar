import { Award, Handshake, Route, type LucideIcon } from "lucide-react";

import { Card, CardDescription, CardTitle } from "@/shared/components/ui/card";
import { HOME_FEATURES } from "./home-features.content";

// FeatureIcon: resolves a content-constant icon key to a lucide icon so
// content stays serializable (data-model.md) while icons render client-lessly.
const ICON_MAP: Record<string, LucideIcon> = {
  Handshake,
  Route,
  Award,
};

function FeatureIcon({ name }: { name: string }) {
  const IconComponent = ICON_MAP[name];
  return (
    <span className="grid size-11 place-items-center rounded-xl bg-secondary-tint text-secondary-text">
      {IconComponent ? (
        <IconComponent className="size-6" strokeWidth={2} aria-hidden="true" />
      ) : null}
    </span>
  );
}

// FeatureCard: one value proposition — icon, title, description.
function FeatureCard({
  icon,
  title,
  description,
}: {
  icon: string;
  title: string;
  description: string;
}) {
  return (
    <Card className="flex h-full flex-col items-start gap-3 p-6">
      <FeatureIcon name={icon} />
      <CardTitle className="text-lg">{title}</CardTitle>
      <CardDescription className="leading-relaxed">{description}</CardDescription>
    </Card>
  );
}

// HomeFeatures: the landing page value propositions.
export function HomeFeatures() {
  return (
    <section className="bg-background py-20 sm:py-24" aria-label="Why choose Masar">
      <div className="mx-auto grid w-full max-w-6xl gap-6 px-6 sm:grid-cols-3">
        {HOME_FEATURES.map((feature) => (
          <FeatureCard key={feature.title} {...feature} />
        ))}
      </div>
    </section>
  );
}