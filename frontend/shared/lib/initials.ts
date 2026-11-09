// Two-letter monogram for an identity avatar, derived from a person's name.
// Shared by the dashboard's identity card and the /profile hero (promoted on
// second use). A missing/blank name yields "?" rather than an empty circle.
export function toInitials(name?: string | null): string {
  const parts = (name ?? "").trim().split(/\s+/).filter(Boolean);

  if (parts.length === 0) return "?";
  if (parts.length === 1) return parts[0].charAt(0).toUpperCase();

  return (
    parts[0].charAt(0) + parts[parts.length - 1].charAt(0)
  ).toUpperCase();
}
