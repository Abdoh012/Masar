"use client";

import { useEffect, useState } from "react";

const API_URL =
  process.env.NEXT_PUBLIC_API_URL ?? "http://localhost:8000/api/v1";

interface SpecializationOption {
  id: number;
  name: string;
}

interface SpecializationState {
  options: SpecializationOption[];
  loading: boolean;
  error: string | null;
}

function extractSpecializations(json: Record<string, unknown>): SpecializationOption[] {
  const data = json.data ?? json;

  if (typeof data === "object" && data !== null && !Array.isArray(data)) {
    const nested = (data as Record<string, unknown>).specializations;
    if (Array.isArray(nested)) return nested as SpecializationOption[];
  }

  if (Array.isArray(data)) return data as SpecializationOption[];

  return [];
}

export function useSpecializationOptions(
  fieldId: string | null,
): SpecializationState {
  const [state, setState] = useState<SpecializationState>({
    options: [],
    loading: false,
    error: null,
  });

  useEffect(() => {
    if (!fieldId) {
      setState({ options: [], loading: false, error: null });
      return;
    }

    let cancelled = false;

    async function fetchSpecializations() {
      setState((prev) => ({ ...prev, loading: true, error: null }));

      try {
        const res = await fetch(
          `${API_URL}/lookups/study-fields/${fieldId}/specializations`,
        );
        if (!res.ok)
          throw new Error(`Failed to load specializations (status ${res.status})`);

        const json = await res.json();
        if (cancelled) return;

        const items = extractSpecializations(json);
        setState({ options: items, loading: false, error: null });
      } catch (err) {
        if (cancelled) return;
        setState({
          options: [],
          loading: false,
          error:
            err instanceof Error ? err.message : "Failed to load specializations",
        });
      }
    }

    fetchSpecializations();
    return () => {
      cancelled = true;
    };
  }, [fieldId]);

  return state;
}
