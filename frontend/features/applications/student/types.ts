// Role-level types for the applications student role (structure rules §14).

export type TrainingMode = "paid_trial" | "part_time" | "full_time";

export interface ActiveApplication {
  id: string;
  company: string;
  listingTitle: string;
  mode: TrainingMode;
  /** Full trial length in days — the countdown ring's denominator. */
  trialDays?: number;
  trialDaysRemaining?: number;
  startedOn: string;
}

export type ApplicationStatus = "Applied" | "Accepted" | "Rejected" | "Withdrawn";

// The five My Applications tabs. Values map 1:1 to the backend endpoints
// (all/applied/accepted/rejected/withdrawn) — see student/api.ts.
export type TabValue = "all" | "applied" | "accepted" | "rejected" | "withdrawn";

// Manual bank-transfer lifecycle on the Accepted card: free trainings never
// require one ("not_required"), paid trainings are "pending" until the company
// confirms the transfer, then "paid".
export type PaymentStatus = "not_required" | "pending" | "paid";

// Company transfer destination shown once the free trial ends on a paid
// accepted training. `bank_account` is null for free trainings and for paid
// trainings whose company has not configured banking details yet.
export interface BankAccount {
  bankName: string | null;
  accountName: string | null;
  accountNumber: string;
  instructions: string | null;
}

// Free-trial state for an accepted application to a paid training. Both fields
// are null for free trainings. `daysRemaining` counts down from the full trial
// once the training starts and floors at 0 (trial over).
export interface ApplicationTrial {
  days: number | null;
  daysRemaining: number | null;
}

// One application card on the My Applications page. Mirrors the backend card
// DTOs (backend docs §1.1–§1.5, built by application_cards.php): every card
// carries the common fields + exactly the status-specific fields of its own
// state — timestamps (`appliedOn` for Applied, `acceptedOn` for Accepted,
// `rejectedOn` for Rejected, `withdrawnOn` for Withdrawn), the trial/payment
// block for Accepted-paid, and rejection info for Rejected. Applied-only:
// `canWithdraw`. Presence reflects the API: absent fields stay undefined.
export interface MyApplication {
  id: number;
  trainingId: number;
  listingTitle: string;
  companyName: string;
  companyLogo: string | null;
  status: ApplicationStatus;
  specialization: string;
  /** Present on every card (the backend always sends applied_at). */
  appliedOn: string;
  acceptedOn?: string;
  rejectedOn?: string;
  withdrawnOn?: string;
  startsAt?: string;
  endsAt?: string;
  /** Remaining calendar days until ends_at (0 once ended, null when absent). */
  duration: number | null;
  isPaid: boolean;
  /** Present (true) only on Applied/Accepted cards; absent on other statuses. */
  mayLeadToHire?: boolean;
  /** Pending applications only — drives the Withdraw entry point. */
  canWithdraw?: boolean;
  rejectionReasonCode?: string;
  rejectionNote?: string | null;
  trial?: ApplicationTrial;
  motivationalMessage?: string;
  paymentStatus?: PaymentStatus;
  bankAccount?: BankAccount | null;
}

export interface StatusCounts {
  applied: number;
  accepted: number;
  rejected: number;
  withdrawn: number;
}

export interface RecentApplicationRow {
  id: string;
  companyName: string;
  listingTitle: string;
  status: ApplicationStatus;
  appliedOn: string;
}

// --- Training application wizard (3-step apply form) ---

export type EducationStatus = "student" | "graduated";

export type TrainingApplicationStep = 1 | 2 | 3;

export interface PersonalInfoValues {
  fullName: string;
  email: string;
  description: string;
  phone: string;
  address: string;
  city: string;
}

export interface EducationValues {
  university: string;
  status: EducationStatus;
  academicYear: string;
  graduationYear: string;
}

export interface TrainingApplicationValues {
  interestReason: string;
  learningGoals: string;
}

export interface ApplicationFormValues {
  personal: PersonalInfoValues;
  education: EducationValues;
  application: TrainingApplicationValues;
}

// Display-only state for the CV upload: name/size drive the step-1 file card.
// The file itself never needs the orchestrator — it rides the form's
// always-mounted <CvFileInput name="cv"> carrier (required), so the step-3
// FormData carries it natively.
export interface CvFileState {
  name: string;
  size: number;
}