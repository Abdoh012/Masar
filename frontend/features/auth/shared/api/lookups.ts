import { serverFetch } from "@/services/api";
import type { TryCatchResponse } from "@/types/server-action";

export interface LookupOption {
  id: number;
  name: string;
}

export function fetchStudyFields(): Promise<TryCatchResponse> {
  return serverFetch({ url: "lookups/study-fields", cache: "no-store" });
}

export function fetchSpecializationsByField(
  fieldId: string,
): Promise<TryCatchResponse> {
  return serverFetch({
    url: `lookups/study-fields/${fieldId}/specializations`,
    cache: "no-store",
  });
}

// Company industry choices are the full active specializations list (shared
// source of truth between students' Specialization and companies' Industry).
export function fetchIndustries(): Promise<TryCatchResponse> {
  return serverFetch({ url: "lookups/specializations", cache: "no-store" });
}