import { Award, Handshake, Route, type LucideIcon } from "lucide-react";

import Motion from "@/shared/components/animation/Motion";
import StaggerGroup from "@/shared/components/animation/StaggerGroup";
import { fadeInUp } from "@/shared/lib/animations";
import { Card, CardDescription, CardTitle } from "@/shared/components/ui/card";
import { HOME_FEATURES } from "./home-features.content";

const ICON_MAP: Record<string, LucideIcon> = {
  Handshake,
  Route,
  Award,
};

interface FeatureCardProps {
  icon: string;
  title: string;
  description: string;
}

// FeatureIcon: resolves a content-constant icon key to a lucide icon so
// content stays serializable (data-model.md) while icons render client-lessly.
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
function FeatureCard({ icon, title, description }: FeatureCardProps) {
  return (
    <Card className="flex h-full flex-col items-start gap-3 p-6">
      <FeatureIcon name={icon} />

      <CardTitle className="text-lg">{title}</CardTitle>

      <CardDescription className="leading-relaxed">
        {description}
      </CardDescription>
    </Card>
  );
}

// HomeFeatures: the landing page value propositions — the grid is the
// stagger container, and each card enters individually in sequence.
export function HomeFeatures() {
  return (
    <section className="bg-background py-20 sm:py-24">
      <StaggerGroup className="mx-auto grid w-full max-w-6xl gap-6 px-6 sm:grid-cols-3">
        {HOME_FEATURES.map((feature) => (
          <Motion key={feature.title} variants={fadeInUp}>
            <FeatureCard {...feature} />
          </Motion>
        ))}
      </StaggerGroup>
    </section>
  );
}