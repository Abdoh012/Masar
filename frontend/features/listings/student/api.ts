import { serverFetch } from "@/services/api";
import type { TryCatchResponse } from "@/types/server-action";

import type { ListingCardData } from "../shared/types";
import { getSavedListings } from "./actions";
import { BrowseParams } from "./lib/browse-params";
import {
  normalizeListResponse,
  normalizeSearchResponse,
} from "./lib/normalize";

export interface Pagination {
  current_page: number;
  per_page: number;
  total: number;
  total_pages: number;
  has_next_page: boolean;
  has_previous_page: boolean;
}

export interface TrainingFilters {
  training_type?: string;
  mode?: string;
  paid?: string;
}

export interface BrowseListingsResult {
  items: ListingCardData[];
  pagination: Pagination;
}

// Selects the right read based on the active browse params (saved-only beats a
// query beats filters beats a plain listing), normalizes the response, and
// throws on a failed read so the caller renders its error boundary. The backend
// computes is_saved/has_applied per authenticated student on each read, so a
// save/unsave revalidation simply re-runs this.
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

  if (query.trim()) {
    const res = await searchListings(query.trim(), page, limit);
    if (res.error) throw new Error(res.error);
    return normalizeSearchResponse(res);
  }

  if (trainingType || mode || paid) {
    const res = await fetchTrainingsFilters(
      { training_type: trainingType, mode, paid },
      page,
      limit,
      sort,
    );
    if (res.error) throw new Error(res.error);
    return normalizeSearchResponse(res);
  }

  const res = await fetchListings(page, limit, sort);
  if (res.error) throw new Error(res.error);
  return normalizeListResponse(res);
}

export function fetchListings(
  page: number,
  limit: number,
  sort: string,
): Promise<TryCatchResponse> {
  const params = new URLSearchParams({
    page: String(page),
    limit: String(limit),
  });
  if (sort && sort !== "default") params.set("sort", sort);
  return serverFetch({ url: `trainings/list?${params}`, cache: "no-store" });
}

export function searchListings(
  query: string,
  page: number,
  limit: number,
): Promise<TryCatchResponse> {
  const params = new URLSearchParams({
    q: query,
    page: String(page),
    limit: String(limit),
  });
  return serverFetch({ url: `search/trainings?${params}`, cache: "no-store" });
}

export function fetchTrainingsFilters(
  filters: TrainingFilters,
  page: number,
  limit: number,
  sort: string,
): Promise<TryCatchResponse> {
  const params = new URLSearchParams({
    page: String(page),
    limit: String(limit),
  });
  if (filters.training_type) params.set("training_type", filters.training_type);
  if (filters.mode) params.set("mode", filters.mode);
  if (filters.paid) params.set("paid", filters.paid);
  if (sort && sort !== "default") params.set("sort", sort);
  return serverFetch({
    url: `search/trainings/filters?${params}`,
    cache: "no-store",
  });
}

export function fetchTrainingDetails(id: string): Promise<TryCatchResponse> {
  return serverFetch({ url: `trainings/details/${id}`, cache: "no-store" });
}
