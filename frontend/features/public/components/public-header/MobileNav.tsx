"use client";

import { AnimatePresence, motion } from "framer-motion";
import { Menu, X } from "lucide-react";
import { useState } from "react";

import { Button } from "@/shared/components/ui/button";
import { headerNavLinks } from "../content/public-nav.content";
import { PublicNavLink } from "./PublicNavLink";

// MobileNav: collapsed header menu on touch screens. Uses framer-motion's
// AnimatePresence to smoothly slide/fade the panel in and out, instead of
// the native details/summary toggle which pops open with no animation.
export function MobileNav() {
  const [open, setOpen] = useState(false);

  return (
    <div className="relative md:hidden">
      {/* Menu button */}
      <button
        type="button"
        onClick={() => setOpen((prev) => !prev)}
        aria-expanded={open}
        aria-label={open ? "Close menu" : "Open menu"}
        className="inline-flex size-11 cursor-pointer items-center justify-center rounded-md text-primary-text transition-[color,background-color,box-shadow] duration-200 ring-2 ring-transparent hover:bg-primary-tint focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
      >
        {open ? (
          <X className="size-5" strokeWidth={2} aria-hidden="true" />
        ) : (
          <Menu className="size-5" strokeWidth={2} aria-hidden="true" />
        )}
      </button>

      {/* Navigation */}
      <AnimatePresence initial={false}>
        {open ? (
          <motion.nav
            key="mobile-nav"
            initial={{ opacity: 0, y: -8, scale: 0.98 }}
            animate={{ opacity: 1, y: 0, scale: 1 }}
            exit={{ opacity: 0, y: -8, scale: 0.98 }}
            transition={{ duration: 0.22, ease: "easeInOut" }}
            className="absolute right-0 top-full z-50 mt-2 w-56 flex-col gap-1 rounded-xl border border-border bg-card p-3 shadow-card-sm origin-top-right"
          >
            {headerNavLinks.map((link) => (
              <PublicNavLink
                key={link.href}
                {...link}
                className="w-full justify-start"
              />
            ))}

            {/* Auth buttons */}
            <div className="mt-1 flex flex-col gap-2 border-t border-border pt-2">
              {/* Sign in button */}
              <Button asChild variant="outline" className="w-full">
                <a href="/sign-in">Sign in</a>
              </Button>

              {/* Sign up button */}
              <Button asChild className="w-full">
                <a href="/sign-up">Start now</a>
              </Button>
            </div>
          </motion.nav>
        ) : null}
      </AnimatePresence>
    </div>
  );
}