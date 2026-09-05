"use client";

import { useEffect, useState } from "react";

import type { LookupOption } from "../api/lookups";
import { fetchStudyFields } from "../api/lookups";

interface StudyFieldsState {
  options: LookupOption[];
  loading: boolean;
  error: string | null;
}

function extractStudyFields(data: unknown): LookupOption[] {
  if (typeof data === "object" && data !== null && !Array.isArray(data)) {
    const fields = (data as Record<string, unknown>).study_fields;
    if (Array.isArray(fields)) return fields as LookupOption[];
  }
  if (Array.isArray(data)) return data as LookupOption[];
  return [];
}

export function useStudyFields(): StudyFieldsState {
  const [state, setState] = useState<StudyFieldsState>({
    options: [],
    loading: true,
    error: null,
  });

  useEffect(() => {
    let cancelled = false;

    async function load() {
      try {
        const res = await fetchStudyFields();
        if (res.error) throw new Error(res.error);
        if (cancelled) return;
        setState({ options: extractStudyFields(res.data), loading: false, error: null });
      } catch (err) {
        if (cancelled) return;
        setState({
          options: [],
          loading: false,
          error: err instanceof Error ? err.message : "Failed to load study fields",
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