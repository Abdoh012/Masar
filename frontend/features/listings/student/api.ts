const API_URL =
  process.env.NEXT_PUBLIC_API_URL ?? "http://localhost:8000/api/v1";

export interface Pagination {
  current_page: number;
  per_page: number;
  total: number;
  total_pages: number;
  has_next_page: boolean;
  has_previous_page: boolean;
}

export interface ListResponse {
  data: {
    items: unknown[];
    pagination: Pagination;
  };
}

export interface SearchResponse {
  data: {
    items: unknown[];
    total: number;
    page: number;
    limit: number;
    query: string;
  };
}

async function apiFetch<T>(path: string): Promise<T> {
  const res = await fetch(`${API_URL}${path}`);
  if (!res.ok) throw new Error(`${res.status}`);
  return res.json();
}

export function fetchListings(
  page: number,
  limit: number,
  sort: string,
): Promise<ListResponse> {
  const params = new URLSearchParams({
    page: String(page),
    limit: String(limit),
  });
  if (sort && sort !== "default") params.set("sort", sort);
  return apiFetch<ListResponse>(`/trainings/list?${params}`);
}

export function searchListings(
  query: string,
  page: number,
  limit: number,
): Promise<SearchResponse> {
  const params = new URLSearchParams({
    q: query,
    page: String(page),
    limit: String(limit),
  });
  return apiFetch<SearchResponse>(`/search/trainings?${params}`);
}

export function fetchTrainingDetails(
  id: string,
): Promise<{ data: Record<string, unknown> }> {
  return apiFetch(`/trainings/details/${id}`);
}
