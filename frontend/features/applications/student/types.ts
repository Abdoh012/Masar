// Role-level types for the applications student role (structure rules §14).

export type TrainingMode = "paid_trial" | "part_time" | "full_time";

export interface ActiveApplication {
  id: string;
  company: string;
  listingTitle: string;
  mode: TrainingMode;
  trialDaysRemaining?: number;
  startedOn: string;
}

export type ApplicationStatus = "Applied" | "Accepted" | "Rejected" | "Withdrawn";

// Trial state for an accepted application to a paid listing. Present only for
// the accepted+paid case (data-model.md presence rules); "Continue past trial"
// is a display-only note, never an action.
export interface ApplicationTrial {
  daysRemaining: number;
  continuePastTrial?: boolean;
}

// One application card on the My Applications page (data-model.md).
export interface MyApplication {
  id: string;
  listingId: string;
  listingTitle: string;
  companyName: string;
  status: ApplicationStatus;
  appliedOn: string;
  rejectionReason?: string;
  mayLeadToHire?: boolean;
  trial?: ApplicationTrial;
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

// --- Training application wizard (3-step apply form, UI-only) ---

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
  skills: string[];
}

export interface ApplicationFormValues {
  personal: PersonalInfoValues;
  education: EducationValues;
  application: TrainingApplicationValues;
}

// The CV upload field is UI-only in this phase: only the file's name/size are
// kept locally for display; no file is uploaded anywhere.
export interface CvFileState {
  name: string;
  size: number;
}