import Link from "next/link";

import { BrandMark } from "@/features/auth";
import { siteConfig } from "@/config/metadata";
import { footerColumns } from "../content/public-nav.content";

interface PublicFooterProps {
  className?: string;
}

// PublicFooter: shared public chrome footer (FR-004). Brand + tagline,
// nav columns from the nav constant, and the copyright line.
export function PublicFooter({ className }: PublicFooterProps) {
  const year = new Date().getFullYear();

  return (
    <footer className="border-t border-border bg-card">
      <div className="mx-auto grid w-full max-w-6xl gap-10 px-6 py-14 sm:grid-cols-2 lg:grid-cols-4">
        <div className="flex flex-col gap-3 sm:col-span-2 lg:col-span-2">
          <BrandMark tone="paper" size="sm" layout="horizontal" />
          <p className="max-w-xs text-sm leading-relaxed text-muted-foreground">
            {siteConfig.tagline}
          </p>
        </div>

        <nav aria-label="Footer" className="grid grid-cols-2 gap-8 sm:col-span-2">
          {footerColumns.map((column) => (
            <div key={column.title} className="flex flex-col gap-3">
              <p className="font-mono text-xs font-semibold uppercase tracking-[0.2em] text-secondary-text">
                {column.title}
              </p>
              <ul className="flex flex-col gap-2">
                {column.links.map((link) => (
                  <li key={link.href}>
                    <Link
                      href={link.href}
                      className="inline-flex text-sm text-muted-foreground transition-[color,background-color,box-shadow] duration-200 ring-2 ring-transparent hover:text-primary-text focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                    >
                      {link.label}
                    </Link>
                  </li>
                ))}
              </ul>
            </div>
          ))}
        </nav>
      </div>

      <div className="border-t border-border">
        <div className="mx-auto w-full max-w-6xl px-6 py-4 text-center">
          <p className="text-sm text-muted-foreground">
            © {year} Masar
          </p>
        </div>
      </div>
    </footer>
  );
}