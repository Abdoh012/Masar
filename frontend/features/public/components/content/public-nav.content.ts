import type { FooterColumn, NavLink } from "../../types";

// Public header + footer navigation (FR-004). Landing is the brand mark;
// the header links to the other four public routes. Footer columns reuse
// the same five public routes grouped into Product and Legal.
export const headerNavLinks: NavLink[] = [
  { label: "About", href: "/about" },
  { label: "Support", href: "/support" },
  { label: "Privacy", href: "/privacy" },
  { label: "Terms", href: "/terms" },
];

export const footerColumns: FooterColumn[] = [
  {
    title: "Explore",
    links: [
      { label: "Home", href: "/" },
      { label: "About", href: "/about" },
      { label: "Support", href: "/support" },
    ],
  },
  {
    title: "Legal",
    links: [
      { label: "Privacy Policy", href: "/privacy" },
      { label: "Terms of Service", href: "/terms" },
    ],
  },
];