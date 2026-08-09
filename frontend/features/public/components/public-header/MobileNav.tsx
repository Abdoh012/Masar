import { Menu } from "lucide-react";

import { Button } from "@/shared/components/ui/button";
import { headerNavLinks } from "../content/public-nav.content";
import { PublicNavLink } from "./PublicNavLink";

// MobileNav: collapses the header nav below md into a native <details>
// disclosure. Native details/ summary makes it keyboard-operable and
// script-free (FR-019/021); the Menu icon is decorative.
export function MobileNav() {
  return (
    <details className="group relative md:hidden">
      <summary
        aria-label="Open navigation"
        className="inline-flex size-11 cursor-pointer list-none items-center justify-center rounded-md text-primary-text transition-[color,background-color,box-shadow] duration-200 ring-2 ring-transparent hover:bg-primary-tint focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring [&::-webkit-details-marker]:hidden"
      >
        <Menu className="size-5" strokeWidth={2} aria-hidden="true" />
      </summary>
      <nav
        aria-label="Mobile navigation"
        className="absolute right-0 top-full z-50 mt-2 w-56 flex-col gap-1 rounded-xl border border-border bg-card p-3 shadow-card-sm"
      >
        {headerNavLinks.map((link) => (
          <PublicNavLink key={link.href} {...link} className="w-full justify-start" />
        ))}
        <div className="mt-1 flex flex-col gap-2 border-t border-border pt-2">
          <Button asChild variant="outline" className="w-full">
            <a href="/sign-in">Sign in</a>
          </Button>
          <Button asChild className="w-full">
            <a href="/sign-up">Start now</a>
          </Button>
        </div>
      </nav>
    </details>
  );
}