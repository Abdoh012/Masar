// Elevation tiers for DashboardSection, ordered by how far the card is meant
// to float off the page. `base` sits flush with its neighbours, `raised` marks
// a surface the user can act on, `hero` is the single card allowed to dominate
// its row. Sections pick a tier; they never restate box classes themselves.
export const ELEVATION_CLASSES = {
  base: "shadow-card",
  raised: "shadow-card-md",
  hero: "shadow-card-lg",
} as const;

export type DashboardElevation = keyof typeof ELEVATION_CLASSES;

// fadeInUp defines the motion but not its timing, so every caller had to
// repeat the same transition inline. The dashboard entrances all want this
// one, so it lives here next to the shell it belongs to.
export const SECTION_ENTRANCE = {
  duration: 0.24,
  ease: "easeOut",
} as const;

// Fire the entrance slightly before the card's top edge reaches the fold.
export const SECTION_VIEWPORT = { once: true, margin: "-40px" };
