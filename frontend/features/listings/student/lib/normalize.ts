import type { ListingCardData, ListingMode } from "../../shared/types";
import type { Pagination } from "../api";

const API_MODE_MAP: Record<string, string> = {
  shadowing: "observer",
};

const API_FORMAT_MAP: Record<string, string> = {
  onsite: "in_person",
};

function computeDuration(
  startsAt?: string,
  endsAt?: string,
): string | undefined {
  if (!startsAt || !endsAt) return undefined;
  const start = new Date(startsAt);
  const end = new Date(endsAt);
  const diffMs = end.getTime() - start.getTime();
  if (diffMs <= 0) return undefined;
  const days = Math.ceil(diffMs / (1000 * 60 * 60 * 24));
  if (days < 30) return `${days} Day${days === 1 ? "" : "s"}`;
  const months = Math.round(days / 30);
  return `${months} Month${months === 1 ? "" : "s"}`;
}

export function normalizeApiItem(
  item: Record<string, unknown>,
): ListingCardData {
  const rawMode = String(item.training_type ?? "observer");
  const mappedMode = API_MODE_MAP[rawMode] ?? rawMode;
  const rawFormat = String(item.mode ?? "remote");
  const mappedFormat = API_FORMAT_MAP[rawFormat] ?? rawFormat;

  const specializations = item.specializations as
    | { id?: number; name?: string }[]
    | undefined;
  const firstSpec = specializations?.[0]?.name;

  const skills = Array.isArray(item.skills)
    ? (item.skills as string[])
    : undefined;

  return {
    id: String(item.id ?? ""),
    companyId: String(item.company_id ?? ""),
    companyName: String(item.company_name ?? ""),
    field: firstSpec ?? String(item.title ?? ""),
    specialization: String(
      item.specialization ?? firstSpec ?? item.title ?? "",
    ),
    description: String(item.description ?? item.title ?? ""),
    mode: mappedMode as ListingMode,
    format: mappedFormat as ListingCardData["format"],
    hireIntent: Boolean(item.hire_intent),
    isPaid: Boolean(item.is_paid),
    price: Number(item.compensation_amount ?? 0),
    trialDays: Number(item.trial_period_days ?? 0),
    status: "published" as const,
    createdAt: String(item.created_at),
    updatedAt: String(item.updated_at),
    skills,
    duration: computeDuration(
      item.starts_at as string | undefined,
      item.ends_at as string | undefined,
    ),
    saved: Boolean(item.is_saved),
    hasApplied: Boolean(item.has_applied),
    companyLogo: String(item.company_logo ?? ""),
  };
}

export function normalizeListResponse(raw: unknown): {
  items: ListingCardData[];
  pagination: Pagination;
} {
  const data = (raw as Record<string, unknown>)?.data ?? raw;
  const body = data as Record<string, unknown>;
  const rawItems = (body.items ?? body.data ?? []) as Record<string, unknown>[];
  const pagination = (body.pagination ?? {
    current_page: 1,
    per_page: 10,
    total: 0,
    total_pages: 0,
    has_next_page: false,
    has_previous_page: false,
  }) as Pagination;

  return {
    items: rawItems.map(normalizeApiItem),
    pagination,
  };
}

export function normalizeSearchResponse(raw: unknown): {
  items: ListingCardData[];
  pagination: Pagination;
} {
  const data = (raw as Record<string, unknown>)?.data ?? raw;
  const body = data as Record<string, unknown>;
  const rawItems = (body.items ?? body.data ?? []) as Record<string, unknown>[];
  const total = Number(body.total ?? 0);
  const page = Number(body.page ?? 1);
  const limit = Number(body.limit ?? 20);
  const totalPages = limit > 0 ? Math.ceil(total / limit) : 0;

  return {
    items: rawItems.map(normalizeApiItem),
    pagination: {
      current_page: page,
      per_page: limit,
      total,
      total_pages: totalPages,
      has_next_page: page < totalPages,
      has_previous_page: page > 1,
    },
  };
}
