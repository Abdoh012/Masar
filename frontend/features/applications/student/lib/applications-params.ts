// URL-driven tab state for the My Applications page. The tab lives in the
// query string (?tab=accepted). Parsing here normalizes any value to a valid
// TabValue; writing happens through the shared createPageUrl helper from the
// client tab bar (mirroring the browse filters/pagination), so every state
// (counts, active tab, per-tab lists) re-derives server-side on navigation —
// the same pattern as features/listings/student/lib/browse-params.ts.
import { TabValue } from "@/features/applications/student/types";

const APPLICATION_TAB_VALUES: TabValue[] = [
  "all",
  "applied",
  "accepted",
  "rejected",
  "withdrawn",
];

/** Parses an unknown ?tab= value into a valid TabValue; anything unknown,
 *  missing, or repeated falls back to "all". */
export function parseApplicationsTab(value: unknown): TabValue {
  if (typeof value === "string" && value !== "all" && value !== "") {
    const match = APPLICATION_TAB_VALUES.find((tab) => tab === value);
    if (match) return match;
  }
  return "all";
}