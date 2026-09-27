// Display copy for the Active Training section (structure rules §14). The
// section's data is fetched by the dashboard orchestrator from
// GET /students/dashboard — nothing here is mock data.
import type { TrainingDeliveryMode } from "../../types";

// Display labels for the delivery-mode pill, keyed by the vocabulary the
// training's own `mode` column uses (in_person | remote | hybrid). The
// dashboard's API sends that raw value, so the pill is a lookup, not a
// derivation. Single source — no inline mapping in the component.
export const TRAINING_MODE_LABELS: Record<TrainingDeliveryMode, string> = {
  in_person: "In-person",
  remote: "Remote",
  hybrid: "Hybrid",
};

// Label shown while the run is still in progress; the number itself is the
// backend's `remaining_days`.
export const DAYS_REMAINING_LABEL = (days: number): string =>
  `${days} ${days === 1 ? "day" : "days"} remaining`;

// The three states lib/training-progress.ts can report, each with the line the
// card shows above the bar. `in_progress` is paired with the day countdown;
// the other two are terminal, so they state the outcome instead.
export const TRAINING_PROGRESS_COPY = {
  not_started: {
    label: "Not started yet",
    subline: "Your training begins on the start date below.",
  },
  in_progress: {
    label: "Training in progress",
    subline: "Progress is measured from your training's start and end dates.",
  },
  completed: {
    label: "Training finished",
    subline: "This training has already reached its end date.",
  },
} as const;
