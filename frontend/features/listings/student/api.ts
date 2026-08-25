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

export interface SaveResponse {
  data: { message: string };
}

async function apiFetch<T>(path: string): Promise<T> {
  const token =
    typeof document !== "undefined"
      ? document.cookie
          .split("; ")
          .find((c) => c.startsWith("masarJwt="))
          ?.split("=")[1]
      : undefined;

  const headers: Record<string, string> = {};
  if (token) headers.Authorization = `Bearer ${token}`;

  const res = await fetch(`${API_URL}${path}`, { headers });
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

export function fetchSavedListings(): Promise<ListResponse> {
  return apiFetch<ListResponse>("/trainings/saved/list");
}

export function fetchTrainingDetails(
  id: string,
): Promise<{ data: Record<string, unknown> }> {
  return apiFetch(`/trainings/details/${id}`);
}

export function saveTraining(id: string): Promise<SaveResponse> {
  return apiFetch<SaveResponse>(`/trainings/save/${id}`);
}

export function unsaveTraining(id: string): Promise<SaveResponse> {
  return apiFetch<SaveResponse>(`/trainings/unsave/${id}`);
}
