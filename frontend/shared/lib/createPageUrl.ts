export default function createPageUrl(
  key: string,
  value: string | number | undefined,
  searchParams?: string,
) {
  // Copy current URL params to preserve existing filters (search, limit, etc.)
  const params = new URLSearchParams(searchParams);

  if (value !== undefined && value !== "") {
    params.set(key, String(value));
  } else {
    params.delete(key);
  }

  if (key !== "page") {
    params.set("page", "1");
  }

  // Reset url to default when page = 1
  if (params.get("page") === "1") {
    params.delete("page");
  }

  return `?${params.toString()}`;
}
