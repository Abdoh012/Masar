import type { ReactNode } from "react";

interface SummaryCardProps {
  label: string;
  value: number;
  cardTint: string;
  iconTint: string;
  icon: ReactNode;
}

// SummaryCard: one lifecycle stat card in the summary strip. The card wears
// its lifecycle state's soft semantic wash (cardTint) plus a matching tinted
// icon tile (iconTint); the number reads as the primary value with the label
// secondary beneath it. Narrow props — the orchestrator resolves tint + icon,
// the leaf only arranges them (§6).
export function SummaryCard({
  label,
  value,
  cardTint,
  iconTint,
  icon,
}: SummaryCardProps) {
  return (
    <div
      className={`flex items-center gap-4 rounded-2xl border border-border p-5 shadow-card ${cardTint}`}
    >
      <span
        className={`flex size-11 shrink-0 items-center justify-center rounded-xl bg-card ${iconTint}`}
      >
        {icon}
      </span>
      <div className="min-w-0">
        <p className="font-sans text-2xl font-bold leading-none text-foreground">
          {value}
        </p>
        <p className="mt-1.5 truncate text-sm text-muted-foreground">{label}</p>
      </div>
    </div>
  );
}