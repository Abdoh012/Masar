// Role-level types for the profiles student (structure rules §14).

export interface StudentProfile {
  name: string;
  field: string;
  initials: string;
  isComplete: boolean;
  studies?: string;
}