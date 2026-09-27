import { SNAPSHOT_COUNT_META } from "./constants";
import type { CertificateSnapshotCounts } from "../../types";

interface CertificateCountsRowProps {
  counts: CertificateSnapshotCounts;
}

// Leaf: the four lifecycle counts under the certificate document. Each wears its
// state's semantic tint from SNAPSHOT_COUNT_META, the same tokens the My
// Certificates page's summary cards use.
//
// The document above shows *one* certificate in full, which leaves the other
// three lifecycle states unrepresented — a student with 5 eligible and 2 in
// review would see an identical card to one with nothing pending. This row is
// what makes the section reflect the API's actual state, and it stays a quiet
// caption so the document remains the subject.
export function CertificateCountsRow({ counts }: CertificateCountsRowProps) {
  return (
    <dl className="mt-5 grid grid-cols-4 gap-2 border-t border-border pt-4">
      {SNAPSHOT_COUNT_META.map(({ key, label, icon: Icon, valueClass }) => (
        <div key={key} className="flex min-w-0 flex-col items-center gap-1">
          <Icon aria-hidden="true" className="size-3.5 text-muted-foreground" />
          <dd
            className={`text-lg font-semibold leading-none tabular-nums ${valueClass}`}
          >
            {counts[key]}
          </dd>
          <dt className="truncate text-[0.6875rem] font-medium uppercase tracking-wide text-muted-foreground">
            {label}
          </dt>
        </div>
      ))}
    </dl>
  );
}
