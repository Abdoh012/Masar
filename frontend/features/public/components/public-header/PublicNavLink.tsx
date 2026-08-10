"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";

interface PublicNavLinkProps {
  label: string;
  href: string;
  className?: string;
}

export function PublicNavLink({ label, href, className }: PublicNavLinkProps) {
  const pathname = usePathname();
  const isActive = pathname === href;

  return (
    <Link
      href={href}
      className={`inline-flex items-center rounded-md px-3 py-2 text-sm font-medium transition-all duration-200 ring-2 ring-transparent focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring",
        ${
          isActive
            ? "text-primary-text"
            : "text-muted-foreground hover:text-primary-text"
        }
        ${className}
      `}
    >
      {label}
    </Link>
  );
}
