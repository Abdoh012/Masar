"use client";

import { useState } from "react";

import { ListingCard } from "../../../shared/components/listing-card/ListingCard";
import { EmptyState } from "../../../shared/components/empty-state/EmptyState";
import type { BrowseListing, ListingFiltersState } from "../../types";

import { ListingFilters } from "./ListingFilters";
import { BROWSE_EMPTY_STATE, DEFAULT_FILTERS, MOCK_BROWSE_LISTINGS } from "./constants";

// Student browse orchestrator (FR-012/013/014). "use client" because it owns
// the filter state. Renders the shared ListingCard grid from the field-scoped
// mock constants, AND-combines mode/format/paid filters, and shows the empty
// state when nothing matches. No fetching, no backend (R-8).

export function BrowseListingsContainer() {
  const [filters, setFilters] = useState<ListingFiltersState>(DEFAULT_FILTERS);

  const visibleListings = MOCK_BROWSE_LISTINGS.filter((listing: BrowseListing) => {
    if (filters.mode && listing.mode !== filters.mode) return false;
    if (filters.format && listing.format !== filters.format) return false;
    if (filters.paid === "free" && listing.isPaid) return false;
    if (filters.paid === "paid" && !listing.isPaid) return false;
    return true;
  });

  return (
    <div className="space-y-6">
      <ListingFilters filters={filters} onChange={setFilters} />

      {visibleListings.length > 0 ? (
        <div className="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
          {visibleListings.map((listing) => (
            <ListingCard key={listing.id} {...listing} />
          ))}
        </div>
      ) : (
        <EmptyState title={BROWSE_EMPTY_STATE.title} message={BROWSE_EMPTY_STATE.message} />
      )}
    </div>
  );
}
