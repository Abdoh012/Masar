export type Role = "student" | "company" | "admin";

export type CompanyStatus = "pending" | "approved" | "rejected";

export interface Session {
  token: string;
  role: Role;
  // Only meaningful when role === "company"
  companyStatus?: CompanyStatus;
}
