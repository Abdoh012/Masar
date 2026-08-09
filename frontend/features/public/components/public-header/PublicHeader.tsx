import Link from "next/link";

import { BrandMark, ThemeToggle } from "@/features/auth";
import { Button } from "@/shared/components/ui/button";
import { headerNavLinks } from "../content/public-nav.content";
import { MobileNav } from "./MobileNav";
import { PublicNavLink } from "./PublicNavLink";

// PublicHeader: the shared sticky public chrome (FR-001/003). Brand mark
// links home; nav covers the four public pages; theme toggle matches the
// auth cluster; Sign in + Start now drive the auth routes.
export function PublicHeader() {
  return (
    <header className="sticky top-0 z-40 border-b border-border bg-background/95 backdrop-blur">
      <div className="mx-auto flex h-16 w-full max-w-6xl items-center justify-between gap-4 px-6">
        <Link href="/" aria-label="Masar home">
          <BrandMark tone="paper" size="sm" layout="horizontal" />
        </Link>

        <nav aria-label="Main" className="hidden items-center gap-1 md:flex">
          {headerNavLinks.map((link) => (
            <PublicNavLink key={link.href} {...link} />
          ))}
        </nav>

        <div className="hidden items-center gap-2 md:flex">
          <ThemeToggle />
          <Button asChild variant="outline">
            <Link href="/sign-in">Sign in</Link>
          </Button>
          <Button asChild>
            <Link href="/sign-up">Start now</Link>
          </Button>
        </div>

        <div className="flex items-center gap-2 md:hidden">
          <ThemeToggle />
          <MobileNav />
        </div>
      </div>
    </header>
  );
}