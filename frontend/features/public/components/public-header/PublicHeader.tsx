import Link from "next/link";

import { BrandMark, ThemeToggle } from "@/features/auth";
import { Button } from "@/shared/components/ui/button";
import { headerNavLinks } from "../content/public-nav.content";
import { MobileNav } from "./MobileNav";
import { PublicNavLink } from "./PublicNavLink";

export function PublicHeader() {
  return (
    <header className="sticky top-0 z-40 border-b border-border bg-background/95 backdrop-blur">
      <div className="mx-auto flex h-16 w-full max-w-6xl items-center justify-between gap-4 px-6">
        <Link href="/">
          <BrandMark tone="paper" size="sm" layout="horizontal" />
        </Link>

        <nav className="hidden items-center gap-1 md:flex">
          {headerNavLinks.map((link) => (
            <PublicNavLink key={link.href} {...link} />
          ))}
        </nav>

        <div className="hidden items-center gap-2 md:flex">
          <ThemeToggle />

          {/* Sign in button */}
          <Button asChild variant="outline">
            <Link href="/sign-in">Sign in</Link>
          </Button>

          {/* Sign up button */}
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
