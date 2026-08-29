"use client";

import { useEffect, useState } from "react";

import type { ListingCardData } from "../../shared/types";
import type { Pagination } from "../api";
import { fetchListings, searchListings } from "../api";
import { getSavedListings } from "../actions";
import {
  normalizeListResponse,
  normalizeSearchResponse,
} from "../lib/normalize";

export interface UseListingsResult {
  listings: ListingCardData[];
  pagination: Pagination;
  loading: boolean;
  error: string | null;
}

export function useListings(params: {
  query: string;
  sort: string;
  savedOnly: boolean;
  page: number;
  limit: number;
  savedVersion?: number;
}): UseListingsResult {
  const { query, sort, savedOnly, page, limit } = params;

  const [listings, setListings] = useState<ListingCardData[]>([]);
  const [pagination, setPagination] = useState<Pagination>({
    current_page: 1,
    per_page: 10,
    total: 0,
    total_pages: 0,
    has_next_page: false,
    has_previous_page: false,
  });

  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    let cancelled = false;

    async function load() {
      setLoading(true);
      setError(null);

      try {
        let result: { items: ListingCardData[]; pagination: Pagination };

        if (savedOnly) {
          const res = await getSavedListings();
          if (res.error) throw new Error(res.error);
          result = normalizeListResponse(res.data);
        } else if (query.trim()) {
          const raw = await searchListings(query.trim(), page, limit);
          result = normalizeSearchResponse(raw);
        } else {
          const raw = await fetchListings(page, limit, sort);
          result = normalizeListResponse(raw);
        }

        if (!cancelled) {
          setListings(result.items);
          setPagination(result.pagination);
          setLoading(false);
        }
      } catch (err) {
        if (!cancelled) {
          setError(
            err instanceof Error ? err.message : "Failed to load trainings",
          );
          setLoading(false);
        }
      }
    }

    load();
    return () => {
      cancelled = true;
    };
  }, [query, sort, savedOnly, page, limit]);
  console.log(listings);

  return { listings, pagination, loading, error };
}
