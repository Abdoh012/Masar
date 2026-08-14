// Static copy for the company pending-approval gate page (structure rules
// §14 — no inline static text in components).
export const PENDING_APPROVAL = {
  heading: "Your account is pending approval",
  description:
    "Your company account was submitted successfully and is currently under review by an administrator. You'll be able to access your dashboard once it's approved.",
  badgeLabel: "Pending Review",
  nextSteps: {
    title: "What happens next",
    items: [
      "An administrator reviews your company account.",
      "Once approved, you'll be able to access your company dashboard.",
      "You don't need to submit your application again.",
    ],
  },
  action: {
    label: "Back to Sign In",
    href: "/sign-in",
  },
  footer: {
    text: "Questions about your account? ",
    linkLabel: "Contact support",
    linkHref: "/support",
  },
} as const;
