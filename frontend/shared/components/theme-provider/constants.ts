// Static/config values for the app-wide theme system. Centralized here so
// the provider and any future theme code share one source of truth.
export const THEME_ATTRIBUTE = "class" as const;
export const THEME_STORAGE_KEY = "masar-theme";
export const THEME_NAMES = ["light", "dark"] as const;
