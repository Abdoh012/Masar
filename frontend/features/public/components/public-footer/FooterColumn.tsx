import Link from "next/link";
import type { NavLink } from "@/features/public/types";

interface FooterColumnProps {
  title: string;
  links: NavLink[];
}

export default function FooterColumn({ title, links }: FooterColumnProps) {
  return (
    <div className="flex flex-col gap-3">
      <p className="font-semibold text-secondary-text">
        {title}
      </p>

      <ul className="flex flex-col gap-2">
        {links.map((link) => (
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
  );
}
