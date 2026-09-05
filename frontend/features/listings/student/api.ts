import { serverFetch } from "@/services/api";
import type { TryCatchResponse } from "@/types/server-action";

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