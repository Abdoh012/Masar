export type AuthResponse = {
  success?: boolean;
  message?: string;
  redirectPath?: string;
  role?: string;
  userStatus?: string;
  error?: string;
  fieldErrors?: Record<string, string[]>;
  userData?: object | null;
};

export type SignupDraft = {
  role: "student" | "company";
  fullName?: string;
  companyName?: string;
  email: string;
  password: string;
  acceptTerms: boolean;
};

export type SignupStepOneValues = {
  fullName?: string;
  companyName?: string;
  email: string;
};

// Profile fields echoed back by the `signup` action when the register call
// rejects, so ProfileInformationForm can re-seed them (React 19 resets
// uncontrolled inputs after a form action). Passwords are never echoed.
export type SignupStepTwoValues = {
  userField?: string;
  specialist?: string;
  university?: string;
  industry?: string;
  description?: string;
};

export type SignInValues = {
  email: string;
};
