const DEFAULT_API_URL = "http://localhost/Masar/backend/api/v1";

// Strip trailing slashes so callers can always append `/<path>` without
// producing a `//` that the backend would not route.
function normalizeBaseUrl(url: string): string {
  return url.replace(/\/+$/, "");
}

export const env = {
  apiUrl: normalizeBaseUrl(
    process.env.NEXT_PUBLIC_API_URL ?? DEFAULT_API_URL,
  ),
  siteUrl: process.env.NEXT_PUBLIC_SITE_URL ?? "http://localhost:3000",
};
