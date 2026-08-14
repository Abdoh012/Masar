import { TRIAL_MIN_DAYS } from "../../../shared/lib/constants";
import type { ListingStatus } from "../../../shared/types";

// Create/edit listing form copy — labels, placeholders, and section titles
// (structure rules §14: no inline static data in components). Mode/format
// option lists and TRIAL_MIN_DAYS are imported from shared/lib/constants.ts
// so this form never redefines platform rules (R-1).

export const FORM_FIELDS = {
  field: {
    label: "Field",
    placeholder: "e.g. Software Engineering",
    required: true,
  },
  specialization: {
    label: "Specialization",
    placeholder: "e.g. Spring Boot Engineer Trainee",
    required: true,
  },
  description: {
    label: "Description",
    placeholder: "Describe the training opportunity",
    required: false,
  },
  price: {
    label: "Price (EGP)",
    placeholder: "e.g. 200",
    required: true,
  },
  trialDays: {
    label: "Trial days",
    placeholder: `e.g. ${TRIAL_MIN_DAYS}`,
    required: true,
  },
} as const;

export const MODE_SECTION = {
  label: "Training mode",
  hint: "How the trainee takes part",
};

export const FORMAT_SECTION = {
  label: "Format",
  hint: "Where the opportunity is delivered",
};

export const PAID_TOGGLE = {
  label: "Paid listing",
  hint: "Charge trainees and show a paid trial period.",
};

export const HIRE_INTENT_LABEL = "I intend to hire after the training";

// Edit-only warning shown above the form when the listing already has
// applicants (FR-005).
export const EDIT_WARNING = {
  title: "This listing has applicants",
  message: "Changes will be visible to applicants.",
};

export const SUBMIT_LABEL = {
  create: "Save listing",
  edit: "Save changes",
} as const;

export const VALIDATION_MESSAGES = {
  required: "Fill in all required fields.",
  price: "Enter a price for the paid listing.",
  trialDays: `Trial days must be at least ${TRIAL_MIN_DAYS}.`,
} as const;

// UI-only placeholder identity for the signed-in company until auth/data
// exists — companyName is resolved from companyId by the data layer later
// (R-2). Never treat these as backend values.
export const CURRENT_COMPANY = {
  id: "c-current-company",
  name: "Your Company",
} as const;

// New listings are created live (published) — the create form has no status
// selector (architecture §1); draft/closed come later via My Listings actions.
export const NEW_LISTING_STATUS: ListingStatus = "published";

// Fallback when an edit-mode listingId matches nothing in the session store
// or the mock list.
export const LISTING_NOT_FOUND = {
  title: "Listing not found",
  message: "This listing may have been removed.",
  backLabel: "Back to My Listings",
};
