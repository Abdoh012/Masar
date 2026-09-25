// Display formatters for the student profile page. Feature-local (only
// profiles needs them). Every parser slices a date-only ISO value before
// constructing a Date so a datetime never yields NaN — the same guard the
// certificates feature's formatShortDate uses — and formats in UTC so a
// "2024-09-12" value doesn't shift a day behind a negative timezone offset.

const MONTH_YEAR = new Intl.DateTimeFormat("en-US", {
  month: "long",
  year: "numeric",
  timeZone: "UTC",
});

const RELATIVE_UNITS: { limit: number; divisor: number; unit: Intl.RelativeTimeFormatUnit }[] = [
  { limit: 60, divisor: 1, unit: "second" },
  { limit: 3600, divisor: 60, unit: "minute" },
  { limit: 86400, divisor: 3600, unit: "hour" },
  { limit: 604800, divisor: 86400, unit: "day" },
  { limit: 2629800, divisor: 604800, unit: "week" },
  { limit: 31557600, divisor: 2629800, unit: "month" },
];

const SECONDS_PER_YEAR = 31557600;

const RELATIVE = new Intl.RelativeTimeFormat("en-US", { numeric: "auto" });

function toUtcDate(isoDate: string): Date {
  const [year, month, day] = isoDate.slice(0, 10).split("-").map(Number);

  if (
    !Number.isFinite(year) ||
    !Number.isFinite(month) ||
    !Number.isFinite(day) ||
    isoDate.length < 10
  ) {
    return new Date(NaN);
  }

  return new Date(Date.UTC(year, month - 1, day));
}

// "2024-09-12" -> "September 2024" for the "Member since" line. Falls back to
// the raw string when the value isn't a real date.
export function formatMonthYear(isoDate: string): string {
  const date = toUtcDate(isoDate);

  if (Number.isNaN(date.getTime())) {
    return isoDate;
  }

  return MONTH_YEAR.format(date);
}

// "2026-09-20T14:30:00" -> "3 hours ago" for a profile-view row. Compare in
// seconds so a value minutes old doesn't round down to "0 seconds".
export function formatRelativeTime(isoDateTime: string): string {
  const timestamp = Date.parse(isoDateTime);

  if (Number.isNaN(timestamp)) {
    return isoDateTime;
  }

  const seconds = Math.max(0, Math.round((Date.now() - timestamp) / 1000));

  for (const { limit, divisor, unit } of RELATIVE_UNITS) {
    if (seconds < limit) {
      return RELATIVE.format(-Math.floor(seconds / divisor), unit);
    }
  }

  return RELATIVE.format(-Math.floor(seconds / SECONDS_PER_YEAR), "year");
}

// Bytes -> "248 KB" / "1.4 MB" for the local file previews.
export function formatFileSize(bytes: number): string {
  if (!Number.isFinite(bytes) || bytes <= 0) {
    return "0 KB";
  }

  const kilobytes = bytes / 1024;

  if (kilobytes < 1024) {
    return `${Math.round(kilobytes)} KB`;
  }

  return `${(kilobytes / 1024).toFixed(1)} MB`;
}
