import { siteConfig } from "@/config/metadata";

import { BrandMark } from "./BrandMark";

export function BrandPanel() {
  return (
    <aside className="relative hidden w-[44%] shrink-0 items-center justify-center overflow-hidden bg-primary-500 px-10 py-20 text-center lg:flex">
      <div className="pointer-events-none absolute -right-28 -top-36 size-[380px] rounded-full border border-neutral-50/10" />
      <div className="pointer-events-none absolute -bottom-32 -left-20 size-64 rounded-full border border-secondary-500/25" />
      <div className="relative z-10 flex max-w-sm flex-col items-center gap-7">
        <BrandMark />
        <p className="text-[11px] font-semibold uppercase tracking-[0.18em] text-neutral-50/50">
          Students &amp; fresh graduates
        </p>
        <p className="text-xl font-normal text-neutral-50/80">
          {siteConfig.tagline}
        </p>
        <span className="rounded-full border border-neutral-50/15 bg-neutral-50/10 px-4 py-1.5 text-xs font-medium text-neutral-50/80">
          Built for Egypt&apos;s students &amp; fresh graduates
        </span>
      </div>
    </aside>
  );
}
