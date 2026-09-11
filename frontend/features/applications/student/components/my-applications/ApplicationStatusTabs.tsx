"use client";

import { useRouter, useSearchParams } from "next/navigation";

import type { Spring } from "framer-motion";

import Motion from "@/shared/components/animation/Motion";
import createPageUrl from "@/shared/lib/createPageUrl";

import type { TabValue } from "../../types";
import { parseApplicationsTab } from "../../lib/applications-params";

export interface ApplicationStatusTab {
  value: TabValue;
  label: string;
  /** Only the All tab carries a count, and only while All is the active view
   *  (that's the only time /all's total is in the fetched response). Status
   *  tabs are label-only — count stays undefined for them. */
  count?: number;
}

interface ApplicationStatusTabsProps {
  tabs: ApplicationStatusTab[];
}

// Spring for the active-pill slide — snappy but visible, the classic
// tabs-with-motion feel. A layoutId animation genuinely can't be expressed
// through the variant system (structure rules §12.1 stock exception), so the
// transition object rides directly on the shared Motion wrapper.
const PILL_SPRING: Spring = { type: "spring", stiffness: 500, damping: 35 };

// ApplicationStatusTabs: single-row tab bar (FR-004/005). Client leaf — the
// tab param is written through the shared createPageUrl helper
// (`router.push(createPageUrl("tab", value, sp))`, the same URL-driven pattern
// as the browse filters/pagination) and read back via useSearchParams, so the
// active tab updates client-side the instant the URL changes. The active tab's
// background is a shared layout-animation pill (Motion + layoutId) that slides
// smoothly to the newly active tab. On narrow screens the bar stays one row
// and scrolls horizontally (FR-033). A count badge renders only when the tab
// carries one — per scope, that's just the All tab while it's active.
export function ApplicationStatusTabs({ tabs }: ApplicationStatusTabsProps) {
  const router = useRouter();
  const searchParams = useSearchParams();
  const sp = searchParams.toString();
  const active = parseApplicationsTab(searchParams.get("tab"));

  const handleSelect = (value: TabValue) => {
    if (value === active) return;
    // "" deletes the param in createPageUrl, so "All" lands on clean
    // /applications; every other tab carries ?tab=<value>.
    router.push(createPageUrl("tab", value === "all" ? "" : value, sp));
  };

  return (
    <div
      className="flex flex-nowrap items-center gap-1 overflow-x-auto rounded-lg border border-border bg-card p-1"
      role="tablist"
      aria-label="Application status"
    >
      {tabs.map((tab) => {
        const isActive = tab.value === active;
        return (
          <button
            key={tab.value}
            type="button"
            role="tab"
            aria-selected={isActive}
            onClick={() => handleSelect(tab.value)}
            className="relative flex shrink-0 items-center whitespace-nowrap rounded-md px-3 py-1.5 text-sm font-medium cursor-pointer"
          >
            {isActive ? (
              <Motion
                as="span"
                layoutId="applications-tab-pill"
                className="absolute inset-0 rounded-md bg-primary"
                transition={PILL_SPRING}
              />
            ) : null}

            <span
              className={`relative z-10 flex items-center gap-1.5 transition-colors ${
                isActive
                  ? "text-primary-foreground"
                  : "text-muted-foreground hover:text-foreground"
              }`}
            >
              {tab.label}
              {tab.count !== undefined ? (
                <span
                  className={`rounded-full px-1.5 py-0.5 text-xs font-semibold transition-colors ${
                    isActive
                      ? "bg-primary-foreground/20 text-primary-foreground"
                      : "bg-secondary-tint text-secondary-text"
                  }`}
                >
                  {tab.count}
                </span>
              ) : null}
            </span>
          </button>
        );
      })}
    </div>
  );
}
