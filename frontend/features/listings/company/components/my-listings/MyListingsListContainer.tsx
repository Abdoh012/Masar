"use client";

import { useState } from "react";
import Link from "next/link";

import { Button } from "@/shared/components/ui/button";

import { useSessionListings } from "../../lib/listing-session";
import { EmptyState } from "../../../shared/components/empty-state/EmptyState";
import type { MyListingRow } from "../../types";

import { ListingRow } from "./ListingRow";
import { LISTING_LIST_LABELS, MOCK_MY_LISTINGS, MY_LISTINGS_TABS } from "./constants";

type TabValue = (typeof MY_LISTINGS_TABS)[number]["value"];

// My Listings orchestrator (FR-007/008). "use client" because it owns the
// All/Published/Closed tab state and subscribes to the session store. Renders
// MOCK_MY_LISTINGS ∪ session rows, session winning by id (R-4/R-8): any row
// created or edited this session replaces the mock row of the same id.

function mergeSessionOverMock(session: MyListingRow[]): MyListingRow[] {
  const byId = new Map<string, MyListingRow>();
  for (const row of MOCK_MY_LISTINGS) byId.set(row.id, row);
  for (const row of session) byId.set(row.id, row);
  return [...byId.values()];
}

export function MyListingsListContainer() {
  const session = useSessionListings();
  const [activeTab, setActiveTab] = useState<TabValue>("all");

  const rows = mergeSessionOverMock(session);
  const visibleRows =
    activeTab === "all" ? rows : rows.filter((row) => row.status === activeTab);

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between gap-4">
        <div className="flex flex-wrap gap-1 rounded-lg border border-border bg-card p-1">
          {MY_LISTINGS_TABS.map((tab) => (
            <button
              key={tab.value}
              type="button"
              onClick={() => setActiveTab(tab.value)}
              aria-pressed={activeTab === tab.value}
              className={`cursor-pointer rounded-md px-3 py-1.5 text-sm font-medium transition-colors ${
                activeTab === tab.value
                  ? "bg-primary text-primary-foreground"
                  : "text-muted-foreground hover:text-foreground"
              }`}
            >
              {tab.label}
            </button>
          ))}
        </div>

        <Button asChild>
          <Link href="/company/listings/new">{LISTING_LIST_LABELS.newListing}</Link>
        </Button>
      </div>

      {visibleRows.length > 0 ? (
        <div className="space-y-4">
          {visibleRows.map((row) => (
            <ListingRow key={row.id} listing={row} />
          ))}
        </div>
      ) : (
        <EmptyState title={LISTING_LIST_LABELS.emptyTitle} message={LISTING_LIST_LABELS.emptyMessage} />
      )}
    </div>
  );
}
