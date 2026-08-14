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