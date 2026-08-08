import type { Role } from "@/types/auth";

// Path prefixes each role is allowed into, outside of PUBLIC_ROUTES.
// Student has no prefix of its own — it's whatever isn't /company or /admin.
export const ROLE_PATH_PREFIXES: Record<Role, string[]> = {
  student: ["/dashboard", "/listings", "/applications", "/certificates", "/messages", "/profile"],
  company: ["/company"],
  admin: ["/admin"],
};
