import { PENDING_APPROVAL } from "./constants";

// PendingNextSteps: the small informational panel explaining what happens
// after a company account is submitted — admin review, dashboard access
// once approved, no re-submission. Gold dots carry the Masar brand accent;
// the panel uses a theme-aware neutral fill so it reads on both the light
// and dark card surfaces.
export function PendingNextSteps() {
  return (
    <div className="rounded-xl border border-border bg-neutral-200/60 p-4 text-left dark:bg-white/[0.04]">
      <p className="mb-3 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
        {PENDING_APPROVAL.nextSteps.title}
      </p>
      <ul className="space-y-2.5">
        {PENDING_APPROVAL.nextSteps.items.map((item) => (
          <li key={item} className="flex items-start gap-2.5">
            <span
              aria-hidden="true"
              className="mt-[0.45em] size-1.5 shrink-0 rounded-full bg-secondary-500"
            />
            <span className="text-sm leading-relaxed text-muted-foreground">
              {item}
            </span>
          </li>
        ))}
      </ul>
    </div>
  );
}
