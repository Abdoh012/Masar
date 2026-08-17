// Public surface for the "auth" feature.
// Sign-up (student/company), sign-in, password reset, company approval
// gate. Nothing outside this feature should ever import from a deeper
// path than this file (R8) — routes and the (auth) layout consume the
// exports below.

export { SignupDraftProvider } from "./shared/hooks/useSignupDraft";
export { ThemeToggle } from "./components/theme-toggle/ThemeToggle";
export { BrandMark } from "./shared/components/BrandMark";
export { BrandPanel } from "./shared/components/BrandPanel";
export { AuthCard } from "./shared/components/AuthCard";
export { AuthPageShell } from "./shared/components/AuthPageShell";
export { SignInForm } from "./components/sign-in/SignInForm";
export { SignUpForm } from "./components/sign-up/SignUpForm";
export { ProfileInformationForm } from "./components/profile-information/ProfileInformationForm";
export { ForgotPasswordForm } from "./components/forget-password/ForgotPasswordForm";
export { ResetPasswordForm } from "./components/reset-password/ResetPasswordForm";
export { OtpForm } from "./components/verify-otp/OtpForm";
