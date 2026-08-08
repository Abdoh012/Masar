import type { ReactNode } from "react";
import { ThemeProvider as NextThemesProvider } from "next-themes";

import { THEME_ATTRIBUTE, THEME_NAMES, THEME_STORAGE_KEY } from "./constants";

// ThemeProvider: Masar's single source of theme state for the app.
//
// Wraps next-themes (the standard provider) rather than hand-rolling
// context — it handles persistence (localStorage), no-FOUC hydration, and
// toggling `.dark` on <html>, which is exactly the class strategy the
// design tokens in app/globals.css are built around (see section 4 there).
//
// Mount once in the root layout, wrapping the whole app:
//   <html> <body> <ThemeProvider> ... </ThemeProvider> </body> </html>
//
// Client components read/change the theme with next-themes' useTheme().
export function ThemeProvider({ children }: { children: ReactNode }) {
  return (
    <NextThemesProvider
      attribute={THEME_ATTRIBUTE}
      defaultTheme="light"
      enableSystem={false}
      storageKey={THEME_STORAGE_KEY}
      themes={[...THEME_NAMES]}
    >
      {children}
    </NextThemesProvider>
  );
}
