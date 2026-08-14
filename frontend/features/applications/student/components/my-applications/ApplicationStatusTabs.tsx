import type { TabValue } from "./constants";

export interface ApplicationStatusTab {
  value: TabValue;
  label: string;
  count: number;
}

interface ApplicationStatusTabsProps {
  tabs: ApplicationStatusTab[];
  active: TabValue;
  onSelect: (value: TabValue) => void;
}

// ApplicationStatusTabs: single-row tab bar with per-tab count badges
// (FR-004/005). On narrow screens the bar stays one row and scrolls
// horizontally to reveal every tab — it never wraps or collapses to a
// dropdown (FR-033). Active tab uses the bg-primary / text-primary-foreground
// treatment from the company My Listings tabs; count chips reuse the feature's
// gold-tint chip convention. Leaf: receives fully-derived tab data (labels +
// counts) from the orchestrator.
export function ApplicationStatusTabs({ tabs, active, onSelect }: ApplicationStatusTabsProps) {
  return (
    <div className="flex flex-nowrap items-center gap-1 overflow-x-auto rounded-lg border border-border bg-card p-1">
      {tabs.map((tab) => {
        const isActive = tab.value === active;
        return (
          <button
            key={tab.value}
            type="button"
            onClick={() => onSelect(tab.value)}
            aria-pressed={isActive}
            className={`flex shrink-0 cursor-pointer items-center gap-1.5 whitespace-nowrap rounded-md px-3 py-1.5 text-sm font-medium transition-colors ${
              isActive
                ? "bg-primary text-primary-foreground"
                : "text-muted-foreground hover:text-foreground"
            }`}
          >
            {tab.label}
            <span
              className={`rounded-full px-1.5 py-0.5 text-xs font-semibold ${
                isActive
                  ? "bg-primary-foreground/20 text-primary-foreground"
                  : "bg-secondary-tint text-secondary-text"
              }`}
            >
              {tab.count}
            </span>
          </button>
        );
      })}
    </div>
  );
}