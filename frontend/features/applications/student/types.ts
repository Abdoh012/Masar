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