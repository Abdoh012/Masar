"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";

import { cn } from "@/shared/lib/utils";
import type { NavLink } from "../../types";

interface PublicNavLinkProps {
  label: string;
  href: string;
  className?: string;
}

// PublicNavLink: header nav item; marks the current public page via
// usePathname so the visitor always knows where they are (US2).
export function PublicNavLink({ label, href, className }: PublicNavLinkProps) {
  const pathname = usePathname();
  const isActive = pathname === href;

  return (
    <Link
      href={href}
      aria-current={isActive ? "page" : undefined}
      className={cn(
        "inline-flex items-center rounded-md px-3 py-2 text-sm font-medium transition-[color,background-color,box-shadow] duration-200 ring-2 ring-transparent focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring",
        isActive
          ? "text-primary-text"
          : "text-muted-foreground hover:text-primary-text",
        className,
      )}
    >
      {label}
    </Link>
  );
}