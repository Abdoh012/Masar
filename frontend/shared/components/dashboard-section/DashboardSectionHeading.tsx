import type { ReactNode } from "react";

// The title row every dashboard section shares: the heading itself, an optional
// leading icon, and an optional trailing slot (view-all link, count chip).
// One place decides how a section announces itself, so the hierarchy reads the
// same across all six. `icon` is a pre-rendered node, never a component
// reference — each section keeps its own icon treatment.
interface DashboardSectionHeadingProps {
  title: string;
  icon?: ReactNode;
  action?: ReactNode;
}

export function DashboardSectionHeading({
  title,
  icon,
  action,
}: DashboardSectionHeadingProps) {
  return (
    <div className="flex items-center justify-between gap-3">
      <h2 className="flex items-center gap-2 text-lg font-semibold tracking-tight text-primary-text">
        {icon}
        {title}
      </h2>

      {action}
    </div>
  );
}
