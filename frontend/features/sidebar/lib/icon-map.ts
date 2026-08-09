import {
  Award,
  Briefcase,
  Building2,
  ClipboardList,
  FileText,
  GraduationCap,
  LayoutDashboard,
  MessageCircle,
  ShieldCheck,
  User,
  Users,
  type LucideIcon,
} from "lucide-react";

// Icon-name registry (contract §4): every `icon` string that appears in
// config/navigation.ts NAV_ITEMS resolves here. Additive — adding a nav item
// with a new icon name requires adding it to this map. Icon components never
// cross a server/client boundary (structure rules §8); items receive the
// icon NAME and resolve it via this map at render time (research R-2).
export const ICON_MAP: Record<string, LucideIcon> = {
  LayoutDashboard,
  GraduationCap,
  FileText,
  Award,
  MessageCircle,
  User,
  Briefcase,
  Users,
  Building2,
  ShieldCheck,
  ClipboardList,
};