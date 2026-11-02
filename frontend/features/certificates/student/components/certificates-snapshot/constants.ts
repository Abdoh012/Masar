import { Award, Clock, ScrollText, Undo2, type LucideIcon } from "lucide-react";

import type { CertificateSnapshotCounts } from "../../types";

// The four lifecycle counts, in the order they read. `key` indexes the counts
// object; the label and icon are this section's own presentation. Each wears
// the lifecycle state's semantic tint — blue for requestable, amber for in
// review, green for issued, neutral for revoked — reusing the same tokens the
// My Certificates page's summary cards use, so a count never changes meaning
// between the two surfaces.
export interface SnapshotCountMeta {
  key: keyof CertificateSnapshotCounts;
  label: string;
  icon: LucideIcon;
  valueClass: string;
}

export const SNAPSHOT_COUNT_META: SnapshotCountMeta[] = [
  {
    key: "eligible",
    label: "Eligible",
    icon: ScrollText,
    valueClass: "text-info-fg",
  },
  {
    key: "pending",
    label: "In review",
    icon: Clock,
    valueClass: "text-warning-fg",
  },
  {
    key: "issued",
    label: "Issued",
    icon: Award,
    valueClass: "text-success-fg",
  },
  {
    key: "revoked",
    label: "Revoked",
    icon: Undo2,
    valueClass: "text-muted-foreground",
  },
];

// The header chip over the document: how many certificates the student holds.
export const CERTIFICATES_ISSUED_LABEL = "issued";

export const CERTIFICATE_DOCUMENT_CAPTION = "Verified certificate";

// The certificate repository stores its dates as MySQL datetimes rendered in the
// backend's own timezone, with no offset attached — "2026-09-16 04:15:01" — so
// `new Date()` would resolve them against the *server's* zone and shift the
// calendar day for half the world. Only the date portion is ever shown, so it is
// read out of the string and rebuilt as a UTC instant: deterministic on every
// host, with no offset to guess. A missing or unparseable value yields an empty
// string, and both the document and its caption omit the clause rather than
// printing "undefined".
export function formatCertificateIssuedOn(issuedAt: string | null): string {
  if (!issuedAt) return "";

  const parts = /^(\d{4})-(\d{2})-(\d{2})/.exec(issuedAt);
  if (!parts) return "";

  const date = new Date(
    Date.UTC(Number(parts[1]), Number(parts[2]) - 1, Number(parts[3])),
  );
  if (Number.isNaN(date.getTime())) return "";

  return new Intl.DateTimeFormat("en-US", {
    day: "numeric",
    month: "short",
    year: "numeric",
    timeZone: "UTC",
  }).format(date);
}
