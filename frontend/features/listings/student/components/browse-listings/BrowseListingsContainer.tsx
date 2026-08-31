"use client";

import { useCallback } from "react";
import { useRouter, useSearchParams } from "next/navigation";

import { ListingCard } from "@/features/listings/shared/components/listing-card/ListingCard";
import { Pagination } from "@/features/listings/shared/components/pagination/Pagination";
import createPageUrl from "@/shared/lib/createPageUrl";

import { useListings } from "../../hooks/useListings";

import { BrowseEmptyState } from "./BrowseEmptyState";
import { BrowseError } from "./BrowseError";
import { BrowseFilterBar } from "./BrowseFilterBar";
import { BrowseGridSkeleton } from "./BrowseGridSkeleton";
import { BrowseHero } from "./BrowseHero";
import { FilterAndSearch } from "./FilterAndSearch";

const PAGE_LIMIT = 10;

export function BrowseListingsContainer() {
  const router = useRouter();
  const searchParams = useSearchParams();
  const sp = searchParams.toString();

  const query = searchParams.get("q") ?? "";
  const sort = searchParams.get("sort") ?? "";
  const savedOnly = searchParams.get("saved") === "1";
  const page = Number(searchParams.get("page") ?? "1");

  const trainingType = searchParams.get("training_type") ?? "";
  const mode = searchParams.get("mode") ?? "";
  const paid = searchParams.get("paid") ?? "";

  const filters = {
    training_type: trainingType || undefined,
    mode: mode || undefined,
    paid: paid || undefined,
  };

  const { listings, pagination, loading, error, removeListing } = useListings({
    query,
    sort,
    savedOnly,
    page,
    limit: PAGE_LIMIT,
    filters,
  });

  const handleUnsaved = useCallback(
    (id: string) => {
      removeListing(id);
    },
    [removeListing],
  );

  const handleSearchChange = useCallback(
    (q: string) => {
      router.push(createPageUrl("q", q, sp));
    },
    [router, sp],
  );

  const handleSortChange = useCallback(
    (sort: string) => {
      router.push(createPageUrl("sort", sort, sp));
    },
    [router, sp],
  );

  const handleSavedOnlyToggle = useCallback(() => {
    const next = searchParams.get("saved") === "1" ? "" : "1";
    router.push(createPageUrl("saved", next, sp));
  }, [router, searchParams, sp]);

  const handleFilterChange = useCallback(
    (key: string, value: string) => {
      router.push(createPageUrl(key, value, sp));
    },
    [router, sp],
  );

  const handleFilterReset = useCallback(() => {
    const params = new URLSearchParams(sp);
    for (const key of ["training_type", "mode", "paid", "sort", "page"]) {
      params.delete(key);
    }
    router.push(`?${params.toString()}`);
  }, [router, sp]);

  return (
    <div>
      <BrowseHero />

      <div className="px-10">
        <FilterAndSearch
          searchQuery={query}
          sort={sort}
          savedOnly={savedOnly}
          onSearchChange={handleSearchChange}
          onSortChange={handleSortChange}
          onSavedOnlyToggle={handleSavedOnlyToggle}
        />

        <BrowseFilterBar
          trainingType={trainingType}
          mode={mode}
          price={paid}
          onTrainingTypeChange={(v) => handleFilterChange("training_type", v)}
          onModeChange={(v) => handleFilterChange("mode", v)}
          onPriceChange={(v) => handleFilterChange("paid", v)}
          onReset={handleFilterReset}
        />

        {loading ? (
          <BrowseGridSkeleton />
        ) : error ? (
          <BrowseError message={error} />
        ) : listings.length === 0 ? (
          <BrowseEmptyState />
        ) : (
          <>
            <div className="mt-6 grid gap-5 sm:grid-cols-2 xl:grid-cols-2">
              {listings.map((listing) => (
                <ListingCard
                  key={listing.id}
                  {...listing}
                  onUnsaved={() => handleUnsaved(listing.id)}
                />
              ))}
            </div>

            <Pagination pagination={pagination} searchParamsString={sp} />
          </>
        )}
      </div>
    </div>
  );
}
