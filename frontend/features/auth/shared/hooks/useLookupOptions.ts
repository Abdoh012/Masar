"use client";

import { useEffect, useState } from "react";

const API_URL =
  process.env.NEXT_PUBLIC_API_URL ?? "http://localhost:8000/api/v1";

export const STUDY_FIELDS_ENDPOINT = `${API_URL}/lookups/study-fields`;
export const SPECIALIZATIONS_ENDPOINT = `${API_URL}/lookups/specializations`;

interface LookupState {
  options: string[];
  loading: boolean;
  error: string | null;
}

function extractItems(json: Record<string, unknown>): { id: number; name: string }[] {
  const data = json.data ?? json;

  if (Array.isArray(data)) return data;

  if (typeof data === "object" && data !== null) {
    for (const key of Object.keys(data)) {
      const val = (data as Record<string, unknown>)[key];
      if (Array.isArray(val)) return val;
    }
  }

  return [];
}

export function useLookupOptions(endpoint: string): LookupState {
  const [state, setState] = useState<LookupState>({
    options: [],
    loading: true,
    error: null,
  });

  useEffect(() => {
    let cancelled = false;

    async function fetchOptions() {
      try {
        const res = await fetch(endpoint);
        if (!res.ok) throw new Error(`Failed to load options (status ${res.status})`);

        const json = await res.json();
        if (cancelled) return;

        const items = extractItems(json).map((item) => item.name ?? "");

        setState({ options: items, loading: false, error: null });
      } catch (err) {
        if (cancelled) return;
        setState({
          options: [],
          loading: false,
          error: err instanceof Error ? err.message : "Failed to load options",
        });
      }
    }

    fetchOptions();
    return () => {
      cancelled = true;
    };
  }, [endpoint]);

  return state;
}
