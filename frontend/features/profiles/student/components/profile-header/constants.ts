// Copy for the dashboard's profile header (structure rules §14). The completion
// percentage itself is not here — it comes from the backend, per render.

export const PROFILE_COMPLETION_CTA = {
  label: "Complete your profile",
} as const;

// The meter is labelled by state rather than by one fixed sentence: a student at
// 0% and one at 100% need different words, and a single "X% complete" line would
// read as an accusation at one end and as nothing at the other. The CTA label
// above is unchanged in every state.
export const PROFILE_COMPLETION_COPY = {
  label: "Profile completion",
  incomplete: (percent: number) =>
    `You're ${percent}% there — a complete profile unlocks tailored training recommendations.`,
  complete: () =>
    "Your profile is complete. Recommendations are tailored to your field.",
} as const;
