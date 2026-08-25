"use client";

import { useEffect, useState } from "react";

const API_URL =
  process.env.NEXT_PUBLIC_API_URL ?? "http://localhost:8000/api/v1";

export const STUDY_FIELDS_ENDPOINT = `${API_URL}/lookups/study-fields`;

interface StudyField {
  id: number;
  name: string;
}

interface StudyFieldsState {
  options: StudyField[];
  loading: boolean;
  error: string | null;
}

function extractStudyFields(json: Record<string, unknown>): StudyField[] {
  const data = json.data ?? json;

  if (typeof data === "object" && data !== null && !Array.isArray(data)) {
    const nested = (data as Record<string, unknown>).study_fields;
    if (Array.isArray(nested)) return nested as StudyField[];
  }

  if (Array.isArray(data)) return data as StudyField[];

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

    async function fetchFields() {
      try {
        const res = await fetch(STUDY_FIELDS_ENDPOINT);
        if (!res.ok)
          throw new Error(`Failed to load study fields (status ${res.status})`);

        const json = await res.json();
        if (cancelled) return;

        const items = extractStudyFields(json);
        setState({ options: items, loading: false, error: null });
      } catch (err) {
        if (cancelled) return;
        setState({
          options: [],
          loading: false,
          error:
            err instanceof Error ? err.message : "Failed to load study fields",
        });
      }
    }

    fetchFields();
    return () => {
      cancelled = true;
    };
  }, []);

  return state;
}
