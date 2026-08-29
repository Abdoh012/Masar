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

  const { listings, pagination, loading, error } = useListings({
    query,
    sort,
    savedOnly,
    page,
    limit: PAGE_LIMIT,
  });

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
    for (const key of ["trainingType", "mode", "price", "sort", "page"]) {
      params.delete(key);
    }
    router.push(`?${params.toString()}`);
  }, [router, sp]);

  const trainingType = searchParams.get("trainingType") ?? "";
  const mode = searchParams.get("mode") ?? "";
  const price = searchParams.get("price") ?? "";

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
          price={price}
          onTrainingTypeChange={(v) => handleFilterChange("trainingType", v)}
          onModeChange={(v) => handleFilterChange("mode", v)}
          onPriceChange={(v) => handleFilterChange("price", v)}
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
                <ListingCard key={listing.id} {...listing} />
              ))}
            </div>

            <Pagination pagination={pagination} searchParamsString={sp} />
          </>
        )}
      </div>
    </div>
  );
}
