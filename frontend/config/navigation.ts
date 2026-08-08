import type { Role } from "@/types/auth";

export interface NavItem {
  label: string;
  href: string;
  // icon name from lucide-react — resolved to a component where it's rendered,
  // never passed as a component reference across the server/client boundary
  icon: string;
}

export const NAV_ITEMS: Record<Role, NavItem[]> = {
  student: [
    { label: "Dashboard", href: "/dashboard", icon: "LayoutDashboard" },
    { label: "Trainings", href: "/listings", icon: "GraduationCap" },
    { label: "My Applications", href: "/applications", icon: "FileText" },
    { label: "Certificates", href: "/certificates", icon: "Award" },
    { label: "Messages", href: "/messages", icon: "MessageCircle" },
    { label: "Profile", href: "/profile", icon: "User" },
  ],
  company: [
    { label: "Dashboard", href: "/company/dashboard", icon: "LayoutDashboard" },
    { label: "My Listings", href: "/company/listings", icon: "Briefcase" },
    { label: "Browse Students", href: "/company/students", icon: "Users" },
    { label: "Messages", href: "/company/messages", icon: "MessageCircle" },
    { label: "Profile", href: "/company/profile", icon: "Building2" },
  ],
  admin: [
    { label: "Companies", href: "/admin/companies", icon: "ShieldCheck" },
    { label: "Listings", href: "/admin/listings", icon: "ClipboardList" },
    { label: "Certificates", href: "/admin/certificates", icon: "Award" },
  ],
};
