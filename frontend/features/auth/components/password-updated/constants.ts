// Static copy for the post-reset "password updated" landing page (structure
// rules §14 — no inline static text in components).
export const REDIRECT_SECONDS = 3;

export const PASSWORD_UPDATED_COPY = {
  heading: "Password updated successfully",
  description: "Your password has been changed and is ready to use.",
  info: "You can now sign in with your new password.",
  redirectPrefix: "Redirecting you to sign in in",
} as const;
