"use client";

import { useEffect, useState } from "react";

import type { LookupOption } from "../api/lookups";
import { fetchIndustries } from "../api/lookups";

interface IndustriesState {
  options: LookupOption[];
  loading: boolean;
  error: string | null;
}

function extractIndustries(data: unknown): LookupOption[] {
  if (typeof data === "object" && data !== null && !Array.isArray(data)) {
    const nested = (data as Record<string, unknown>).specializations;
    if (Array.isArray(nested)) return nested as LookupOption[];
  }
  if (Array.isArray(data)) return data as LookupOption[];
  return [];
}

export function useIndustries(): IndustriesState {
  const [state, setState] = useState<IndustriesState>({
    options: [],
    loading: true,
    error: null,
  });

  useEffect(() => {
    let cancelled = false;

    async function load() {
      try {
        const res = await fetchIndustries();
        if (res.error) throw new Error(res.error);
        if (cancelled) return;
        setState({ options: extractIndustries(res.data), loading: false, error: null });
      } catch (err) {
        if (cancelled) return;
        setState({
          options: [],
          loading: false,
          error: err instanceof Error ? err.message : "Failed to load industries",
        });
      }
    }

    load();
    return () => {
      cancelled = true;
    };
  }, []);

  return state;
}