interface ActiveTrainingCardProps {
  company: string;
  listingTitle: string;
}

export default function ActiveTrainingCard({
  company,
  listingTitle,
}: ActiveTrainingCardProps) {
  return (
    <div className="flex items-center gap-3 rounded-xl bg-background p-3">
      {/* Company */}
      <span className="flex size-10 shrink-0 items-center justify-center rounded-full bg-primary-tint text-sm font-semibold text-primary-text">
        {company.charAt(0)}
      </span>

      {/* Title */}
      <div className="min-w-0">
        <p className="text-[11px] font-semibold uppercase tracking-[0.08em] text-muted-foreground">
          {company}
        </p>
        <p className="truncate text-sm font-medium text-foreground">
          {listingTitle}
        </p>
      </div>
    </div>
  );
}
