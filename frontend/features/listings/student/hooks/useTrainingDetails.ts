"use client";

import { useEffect, useState } from "react";

import type { ListingCardData } from "../../shared/types";
import { fetchTrainingDetails } from "../api";
import { normalizeApiItem } from "../lib/normalize";

export interface UseTrainingDetailsResult {
  listing: ListingCardData | null;
  loading: boolean;
  error: string | null;
}

export function useTrainingDetails(id: string): UseTrainingDetailsResult {
  const [listing, setListing] = useState<ListingCardData | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    let cancelled = false;

    async function load() {
      setLoading(true);
      setError(null);

      try {
        const raw = await fetchTrainingDetails(id);
        if (!cancelled) {
          setListing(normalizeApiItem(raw.data));
          setLoading(false);
        }
      } catch (err) {
        if (!cancelled) {
          setError(err instanceof Error ? err.message : "Failed to load training");
          setLoading(false);
        }
      }
    }

    load();
    return () => {
      cancelled = true;
    };
  }, [id]);

  return { listing, loading, error };
}
