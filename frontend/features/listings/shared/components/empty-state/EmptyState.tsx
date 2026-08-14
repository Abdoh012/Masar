interface EmptyStateProps {
  title: string;
  message: string;
}

// EmptyState: the shared "nothing here / no matches" panel used by the
// company list, student browse, and admin moderation when their filtered
// result is empty. Pure leaf — takes only the copy it renders. One
// definition instead of three duplicated dashed-border panels (FR-008/013/018).
export function EmptyState({ title, message }: EmptyStateProps) {
  return (
    <div className="rounded-xl border border-dashed border-border p-10 text-center">
      <p className="font-sans text-base font-semibold text-foreground">{title}</p>
      <p className="mt-1 text-sm text-muted-foreground">{message}</p>
    </div>
  );
}