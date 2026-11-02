import type { ReactNode } from "react";

import { cn } from "@/shared/lib/utils";

interface DashboardStatCardProps {
  label: string;
  value: number;
  description: string;
  /** Pre-rendered glyph, never a component reference (§8). */
  icon: ReactNode;
  /** Tailwind text colour class for the card's own accent. Each counter gets a
   *  different one so the three read as three separate measures rather than
   *  one repeated tile. */
  accentClass: string;
}

// DashboardStatCard: leaf — one headline counter, laid out as a vertical KPI:
// label and icon across the top, the figure as the card's subject, the
// explanation beneath it.
//
// Deliberately the opposite shape to the Applications snapshot's status tiles.
// Those are a compact horizontal band that earns its colour from a lifecycle
// state; these are tall cards where a large figure is the whole point. Keeping
// the two card families visibly different is what stops the dashboard reading
// as six interchangeable boxes, and it is why this card leads with the number
// rather than with an icon disc.
//
// The accent is a token-only corner wash plus the icon tile, never a tinted
// panel: these are totals, not states, so colour here would imply a meaning
// they don't carry. `tabular-nums` keeps the figure from reflowing as it counts
// up between digits.
export function DashboardStatCard({
  label,
  value,
  description,
  icon,
  accentClass,
}: DashboardStatCardProps) {
  return (
    <div
      className={cn(
        "group relative flex flex-col overflow-hidden rounded-2xl border border-border bg-card p-5 shadow-card transition-shadow duration-200 hover:shadow-card-md",
      )}
    >
      {/* Corner wash — a soft tinted glow bleeding off the top-right, the same
          token-only treatment the page header uses. `bg-current` lets the card's
          single `accentClass` (a text colour) drive the wash too, so the figure
          and its glow can never drift apart. */}
      <span
        aria-hidden="true"
        className={cn(
          "pointer-events-none absolute -right-10 -top-12 size-32 rounded-full bg-current opacity-[0.07] blur-2xl transition-opacity duration-300 group-hover:opacity-[0.12]",
          accentClass,
        )}
      />

      <div className="relative flex items-start justify-between gap-3">
        <p className="text-xs font-semibold uppercase tracking-wide text-muted-foreground">
          {label}
        </p>
        <span
          className={cn(
            "flex size-10 shrink-0 items-center justify-center rounded-xl bg-primary-tint text-primary-text",
          )}
        >
          {icon}
        </span>
      </div>

      <p
        className={cn(
          "relative mt-5 text-4xl font-semibold leading-none tracking-tight tabular-nums",
          accentClass,
        )}
      >
        {value}
      </p>

      <p className="relative mt-2.5 text-xs leading-relaxed text-muted-foreground">
        {description}
      </p>
    </div>
  );
}
