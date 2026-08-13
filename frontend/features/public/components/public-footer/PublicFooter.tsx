import { BrandMark } from "@/features/auth";
import { siteConfig } from "@/config/metadata";
import { footerColumns } from "../content/public-nav.content";
import FooterColumn from "./FooterColumn";

export function PublicFooter() {
  const year = new Date().getFullYear();

  return (
    <footer className="border-t border-border bg-card">
      <div className="mx-auto grid w-full max-w-6xl gap-10 px-6 py-14 sm:grid-cols-2 lg:grid-cols-4">
        {/* Brand and tagline */}
        <div className="flex flex-col gap-3 sm:col-span-2 lg:col-span-2">
          <BrandMark chip="dark" size="sm" layout="horizontal" />
          <p className="max-w-xs text-sm leading-relaxed text-muted-foreground">
            {siteConfig.tagline}
          </p>
        </div>

        {/* Navigation */}
        <nav className="grid grid-cols-2 gap-8 sm:col-span-2">
          {footerColumns.map((column) => (
            <FooterColumn key={column.title} {...column} />
          ))}
        </nav>
      </div>

      {/* Bottom bar — logo + copyright + social (optional). */}
      <div className="border-t border-border">
        <div className="mx-auto w-full max-w-6xl px-6 py-4 text-center">
          <p className="text-sm text-muted-foreground">© {year} Masar</p>
        </div>
      </div>
    </footer>
  );
}
