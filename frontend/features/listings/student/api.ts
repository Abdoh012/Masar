import { serverFetch } from "@/services/api";
import type { TryCatchResponse } from "@/types/server-action";

import type { ListingCardData } from "../shared/types";
import { getSavedListings } from "./actions";
import { BrowseParams } from "./lib/browse-params";
import { normalizeListResponse, normalizeSearchResponse } from "./lib/normalize";

export interface Pagination {
  current_page: number;
  per_page: number;
  total: number;
  total_pages: number;
  has_next_page: boolean;
  has_previous_page: boolean;
}

export interface SearchFilters {
  q?: string;
  training_type?: string;
  mode?: string;
  paid?: string;
}

export interface BrowseListingsResult {
  items: ListingCardData[];
  pagination: Pagination;
}

// Saved-only is a dedicated endpoint; everything else (query, filters, plain
// browse) goes through the single search/trainings endpoint with all params.
export async function fetchBrowseListings(
  params: BrowseParams,
  limit: number,
): Promise<BrowseListingsResult> {
  const { query, sort, savedOnly, page, trainingType, mode, paid } = params;

  if (savedOnly) {
    const res = await getSavedListings();
    if (res.error) throw new Error(res.error);
    return normalizeListResponse(res.data);
  }

  const res = await searchTrainings({
    q: query.trim() || undefined,
    training_type: trainingType || undefined,
    mode: mode || undefined,
    paid: paid || undefined,
    page,
    limit,
    sort,
  });
  if (res.error) throw new Error(res.error);
  return normalizeSearchResponse(res);
}

export function searchTrainings(filters: SearchFilters & {
  page: number;
  limit: number;
  sort?: string;
}): Promise<TryCatchResponse> {
  const params = new URLSearchParams({
    page: String(filters.page),
    limit: String(filters.limit),
  });
  if (filters.q) params.set("q", filters.q);
  if (filters.training_type) params.set("training_type", filters.training_type);
  if (filters.mode) params.set("mode", filters.mode);
  if (filters.paid) params.set("paid", filters.paid);
  if (filters.sort && filters.sort !== "default") params.set("sort", filters.sort);
  return serverFetch({ url: `search/trainings?${params}`, cache: "no-store" });
}

export function fetchTrainingDetails(id: string): Promise<TryCatchResponse> {
  return serverFetch({ url: `trainings/details/${id}`, cache: "no-store" });
}
