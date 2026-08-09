import { cn } from "@/shared/lib/utils";

import { SealMark } from "./SealMark";
import type { CertificateDocument as CertificateDocumentData } from "../../types";

interface CertificateCornerProps {
  position: "tl" | "tr" | "bl" | "br";
  variant: "paper" | "navy";
}

function CertificateCorner({ position, variant }: CertificateCornerProps) {
  const positionClasses = {
    tl: "left-[14px] top-[14px] border-r-0 border-b-0",
    tr: "right-[14px] top-[14px] border-l-0 border-b-0",
    bl: "bottom-[14px] left-[14px] border-r-0 border-t-0",
    br: "bottom-[14px] right-[14px] border-l-0 border-t-0",
  }[position];

  return (
    <span
      aria-hidden="true"
      className={cn(
        "pointer-events-none absolute size-[18px] border-[1.5px]",
        variant === "navy" ? "border-secondary-500" : "border-secondary-500",
        positionClasses,
      )}
    />
  );
}

export interface CertificateDocumentProps {
  data: CertificateDocumentData;
  variant?: "paper" | "navy";
  compact?: boolean;
}

// CertificateDocument: the fixed Masar certificate artifact from
// masar-identity.html (navy border, gold frame + corners, seal, serif name,
// mono ID, signature line). Stays a consistent physical document in both
// themes — fixed brand colors are used, not adaptive tokens.
export function CertificateDocument({
  data,
  variant = "paper",
  compact = false,
}: CertificateDocumentProps) {
  const isNavy = variant === "navy";

  return (
    <div
      className={cn(
        "relative flex flex-col items-center border-2 text-center",
        isNavy
          ? "border-secondary-500 bg-primary-500 text-white"
          : "border-primary-500 bg-neutral-100 text-neutral-800",
        compact ? "gap-1 px-6 py-7 sm:px-8" : "gap-1.5 border-2 px-8 py-10 sm:px-12",
      )}
    >
      {/* inner gold frame */}
      <span
        aria-hidden="true"
        className="pointer-events-none absolute inset-[10px] border-[1px] border-secondary-500"
      />

      <CertificateCorner position="tl" variant={variant} />
      <CertificateCorner position="tr" variant={variant} />
      <CertificateCorner position="bl" variant={variant} />
      <CertificateCorner position="br" variant={variant} />

      <div className="flex items-center gap-2">
        <SealMark className={cn("size-5", isNavy ? "opacity-90" : "")} />
        <span
          className={cn(
            "text-[13px] font-bold tracking-[0.16em]",
            isNavy ? "text-white" : "text-primary-500",
          )}
        >
          MASAR
        </span>
      </div>

      <p
        className={cn(
          "mt-1 text-[10px] font-semibold uppercase tracking-[0.22em]",
          isNavy ? "text-white/55" : "text-neutral-700",
        )}
      >
        Certificate of Completion
      </p>

      <p
        className={cn(
          "font-serif italic",
          isNavy ? "text-white/55" : "text-neutral-700",
          compact ? "text-[13px]" : "text-[14px]",
        )}
      >
        This certifies that
      </p>

      <p
        className={cn(
          "border-b font-serif",
          { "border-white/25": isNavy, "border-neutral-400": !isNavy },
          isNavy ? "text-secondary-500" : "text-primary-500",
          "pb-2",
          compact ? "text-[22px]" : "text-[32px]",
        )}
      >
        {data.studentName}
      </p>

      <p
        className={cn(
          "font-serif leading-[1.85]",
          isNavy ? "text-white/85" : "text-neutral-800",
          compact ? "max-w-sm text-[13px]" : "max-w-md text-[14px]",
        )}
      >
        has successfully completed{" "}
        <strong className={cn("font-semibold", isNavy ? "text-white" : "text-primary-500")}>
          {data.title}
        </strong>{" "}
        in{" "}
        <strong className={cn("font-semibold", isNavy ? "text-white" : "text-primary-500")}>
          {data.field}
        </strong>{" "}
        with{" "}
        <strong className={cn("font-semibold", isNavy ? "text-white" : "text-primary-500")}>
          {data.companyName}
        </strong>
        {data.issuedOn ? `and was issued on ${data.issuedOn}` : null}
      </p>

      <div className={cn("flex w-full items-end justify-between", compact ? "mt-4" : "mt-6")}>
        <div className="flex w-28 flex-col items-center gap-1">
          <SealMark className="size-9" />
          <span className={cn("text-[10px] font-medium", isNavy ? "text-white/55" : "text-neutral-700")}>
            Verified by Masar
          </span>
        </div>

        <span className={cn("font-mono text-[10.5px] tracking-[0.04em]", isNavy ? "text-white/50" : "text-neutral-700")}>
          {data.certId}
        </span>

        <div className="flex w-28 flex-col items-center gap-1">
          <span aria-hidden="true" className={cn("w-full border-t", isNavy ? "border-white/60" : "border-neutral-800")} />
          <span className={cn("text-[10px] font-medium", isNavy ? "text-white/55" : "text-neutral-700")}>
            {data.companyName}
          </span>
        </div>
      </div>
    </div>
  );
}