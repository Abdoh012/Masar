import type {
  ApplicationFormValues,
  EducationStatus,
  TrainingApplicationStep,
} from "../../types";

// Training application wizard constants (structure rules §14 — no inline data).
// All copy, field configs, and the skills catalog live here so the components
// stay presentational. The student's known profile details are prefilled
// locally; the wizard submits to POST /api/v1/applications via the feature's
// submitApplication action.

// The student's known profile details, used to prefill the apply form so
// already-known information is not re-entered. Matches the profiles feature's
// mock identity; kept local because features never import each other (R6).
export const MOCK_STUDENT_PROFILE = {
  fullName: "",
  email: "",
  university: "",
};

export const INITIAL_VALUES: ApplicationFormValues = {
  personal: {
    fullName: MOCK_STUDENT_PROFILE.fullName,
    email: MOCK_STUDENT_PROFILE.email,
    description: "",
    phone: "",
    address: "",
    city: "",
  },
  education: {
    university: MOCK_STUDENT_PROFILE.university,
    status: "student",
    academicYear: "",
    graduationYear: "",
  },
  application: {
    interestReason: "",
    learningGoals: "",
    skills: [],
  },
};

// Progress indicator steps, in flow order.
export const STEPS = [
  { label: "Personal Information" },
  { label: "Education" },
  { label: "Training Application" },
] as const;

// Heading above the desktop vertical progress rail.
export const PROGRESS_RAIL_HEADING = "Application Progress";

// Per-step header copy (the same labels drive the progress indicator).
export const STEP_HEADERS: Record<
  TrainingApplicationStep,
  { title: string; description: string }
> = {
  1: {
    title: "Personal Information",
    description:
      "Tell us a little about yourself and provide your contact details to get started with your application.",
  },
  2: {
    title: "Education",
    description:
      "Provide your current academic information so we can better understand your educational background.",
  },
  3: {
    title: "Training Application",
    description:
      "Tell us why you're interested in this training, what you hope to learn, and what skills you can bring.",
  },
};

// Step 1 field configs. `description` is the only native-optional field; text
// fields use native `required`. The CV is also required but gated manually by
// the orchestrator (its carrier input is sr-only, so a native bubble would
// anchor to the top of the form instead of the field) — see
// CV_FIELD_LABELS.requiredError.
export const PERSONAL_INFO_FIELDS = {
  fullName: {
    label: "Full Name",
    type: "text",
    placeholder: "e.g. Nour El-Sayed",
    autoComplete: "name",
  },
  email: {
    label: "Email",
    type: "email",
    placeholder: "you@example.com",
    autoComplete: "email",
  },
  description: {
    label: "Description",
    type: "textarea",
    placeholder: "Tell the company a little about yourself...",
    optional: true,
  },
  phone: {
    label: "Phone Number",
    type: "tel",
    placeholder: "e.g. 0100 123 4567",
    autoComplete: "tel",
  },
  address: {
    label: "Address",
    type: "text",
    placeholder: "e.g. 12 Nile Street, Downtown",
    autoComplete: "street-address",
  },
  city: {
    label: "City",
    type: "text",
    placeholder: "e.g. Cairo",
    autoComplete: "address-level2",
  },
} as const;

// Step 2 field configs + the status options driving the conditional year field.
export const EDUCATION_FIELDS = {
  university: { label: "University", placeholder: "e.g. Cairo University" },
  status: { label: "Current Status" },
  academicYear: { label: "Academic Year", placeholder: "e.g. Third Year" },
  graduationYear: { label: "Graduation Year", placeholder: "e.g. 2026" },
} as const;

export const EDUCATION_STATUS_OPTIONS: {
  value: EducationStatus;
  label: string;
}[] = [
  { value: "student", label: "Still a student" },
  { value: "graduated", label: "Graduated" },
];

// Step 3 field configs + the selectable skills catalog. Skills are optional.
export const TRAINING_APPLICATION_FIELDS = {
  interestReason: {
    label: "Why are you interested in this training?",
    placeholder:
      "Tell us what draws you to this training and how it fits your goals...",
  },
  learningGoals: {
    label: "What do you hope to learn?",
    placeholder: "Share the skills and knowledge you want to walk away with...",
  },
  skills: { label: "Skills" },
} as const;

export const SKILL_OPTIONS = [
  "Java",
  "Spring Boot",
  "React",
  "TypeScript",
  "SQL",
  "PostgreSQL",
  "Docker",
  "CI/CD",
  "Linux",
  "QA",
  "Test Automation",
  "Figma",
  "Prototyping",
  "SEO",
  "Social Media",
  "Excel",
] as const;

export const CV_FIELD_LABELS = {
  label: "Upload CV / Student Information",
  empty: "Click to upload your CV",
  hint: "PDF or Word — max 5 MB",
  remove: "Remove",
  requiredError: "Please select a file",
} as const;

export const NAVIGATION_LABELS = {
  back: "Back",
  backToListing: "Back to listing",
  continue: "Continue",
  submit: "Submit Application",
  submitting: "Submitting...",
} as const;

export const SUCCESS_COPY = {
  title: "Application submitted",
  message: (listingTitle: string, companyName: string) =>
    `Your application to ${listingTitle} at ${companyName} has been received. The company will reach out if you're shortlisted.`,
  backToBrowse: "Back to browse",
  viewApplications: "View my applications",
} as const;

export function formatFileSize(bytes: number): string {
  if (bytes < 1024) return `${bytes} B`;
  if (bytes < 1024 * 1024) return `${Math.round(bytes / 1024)} KB`;
  return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
}
