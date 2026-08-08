"use client";

import { Moon, Sun } from "lucide-react";
import { useTheme } from "next-themes";

import { cn } from "@/shared/lib/utils";

// ThemeToggle: pill switch for light/dark mode. Reads the current theme
// from the global ThemeProvider (next-themes, mounted in the root layout)
// and flips it via setTheme — it renders a track+knob switch with a
// sun/moon icon, following masar-identity.html's toggle. The icon/state
// stays in sync with the provider, which also updates <html class="dark">
// so the app/globals.css tokens respond. Positional classes (e.g. the
// fixed corner placement on the auth page) come in through className.
//
// Transition notes (matching masar-identity.html's theme-toggle CSS):
//   - The button itself has no transition — its colors snap with the page
//     so the control never lags behind the instant theme flip (which
//     previously read as a blink/flicker).
//   - Only the track + knob animate (200ms), so the slide is the single,
//     deliberate motion. Tailwind v4's translate-x-* drives the knob via
//     the `translate` property, so the transition must target `translate`
//     (not `transform`) or the knob would jump instead of sliding.
//   - Everything is disabled under prefers-reduced-motion.
interface ThemeToggleProps {
  className?: string;
}

export function ThemeToggle({ className }: ThemeToggleProps) {
  const { theme, setTheme } = useTheme();
  const isDark = theme === "dark";

  return (
    <button
      type="button"
      role="switch"
      aria-checked={isDark}
      aria-label="Toggle dark theme"
      onClick={() => setTheme(isDark ? "light" : "dark")}
      className={cn(
        "inline-flex items-center gap-2 rounded-full border border-border bg-card p-1.5 text-xs font-semibold text-muted-foreground shadow-card-sm hover:border-primary/40 hover:text-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:ring-offset-background active:scale-95 sm:pl-3",
        className,
      )}
    >
      <span className="hidden sm:inline" aria-hidden="true">
        {isDark ? "Dark" : "Light"}
      </span>
      <span
        className="relative cursor-pointer inline-flex h-5 w-9 shrink-0 items-center rounded-full bg-primary-tint transition-colors duration-200 motion-reduce:transition-none"
        aria-hidden="true"
      >
        <span
          className={cn(
            "grid h-4 w-4 translate-x-0.5 place-items-center rounded-full bg-primary text-primary-foreground shadow-sm transition-[translate,background-color] duration-200 motion-reduce:transition-none",
            isDark && "translate-x-4 bg-secondary text-secondary-foreground",
          )}
        >
          <Sun
            className={cn(
              "col-start-1 row-start-1 h-2.5 w-2.5 transition-opacity duration-200 motion-reduce:transition-none",
              isDark && "opacity-0",
            )}
            strokeWidth={2.5}
          />
          <Moon
            className={cn(
              "col-start-1 row-start-1 h-2.5 w-2.5 transition-opacity duration-200 motion-reduce:transition-none",
              !isDark && "opacity-0",
            )}
            strokeWidth={2.5}
          />
        </span>
      </span>
    </button>
  );
}
