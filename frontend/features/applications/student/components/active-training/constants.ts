// Mock active-training data (UI-only; the real slice is fetched by this section in a later phase).

import type { ActiveApplication, TrainingMode } from "../../types";

// Display labels for the mode pill (single source — no inline mapping in the component).
export const TRAINING_MODE_LABELS: Record<TrainingMode, string> = {
  paid_trial: "Paid trial",
  part_time: "Part-time",
  full_time: "Full-time",
};

export const ACTIVE_TRAINING: ActiveApplication = {
  id: "app-1042",
  company: "Hala Bank",
  listingTitle: "Software Engineering Trainee",
  mode: "paid_trial",
  trialDaysRemaining: 12,
  startedOn: "2026-07-20",
};

// Non-trial variant: countdown hidden, normal presentation.
export const ACTIVE_TRAINING_FULL_TIME: ActiveApplication = {
  ...ACTIVE_TRAINING,
  mode: "full_time",
  trialDaysRemaining: undefined,
};

// Empty variant: no active training → NoActiveTraining state. Typed so flipping
// the ActiveTraining read to this stays type-safe.
export const ACTIVE_TRAINING_NULL: ActiveApplication | null = null;
