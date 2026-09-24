import type { EndedApplication } from "../../../types";
import { formatApplicationDate } from "../constants";
import { ENDED_CARD_LABELS, formatDegree } from "./constants";
import { EndedApplicationCardActions } from "./EndedApplicationCardActions";

interface EndedApplicationCardProps {
  ended: EndedApplication;
}

// EndedApplicationCard: server leaf for one completed training on the Ended
// Applications tab. Renders the "Completed" badge, training title + company,
// the completion date (backend end_date — the day the training finished,
// formatted via the section's Africa/Cairo short-date helper), a facts panel
// with the achieved degree and the training specialization (each row hides
// when the API omits the field), and the View Certificate action. All
// interactivity lives in the client EndedApplicationCardActions leaf.
export function EndedApplicationCard({ ended }: EndedApplicationCardProps) {
  const degree = formatDegree(ended.gradeLabel, ended.grade);

  return (
    <div className="flex h-full flex-col gap-3 rounded-xl border border-border bg-card p-5">
      <span className="self-start rounded-full bg-success-bg px-2.5 py-0.5 text-xs font-medium text-success-fg">
        {ENDED_CARD_LABELS.completed}
      </span>

      <div className="space-y-0.5">
        <p className="truncate font-sans text-base font-semibold text-foreground">
          {ended.listingTitle}
        </p>
        <p className="truncate text-sm text-muted-foreground">
          {ended.companyName}
        </p>
      </div>

      <time
        className="font-mono text-xs text-muted-foreground"
        dateTime={ended.completedOn}
      >
        {ENDED_CARD_LABELS.completedOn}{" "}
        {formatApplicationDate(ended.completedOn)}
      </time>

      <div className="space-y-1 rounded-lg bg-neutral-badge-bg/60 px-3 py-2">
        {degree ? (
          <p className="text-xs text-muted-foreground">
            <span className="font-semibold text-neutral-badge-fg">
              {ENDED_CARD_LABELS.degree}:{" "}
            </span>
            {degree}
          </p>
        ) : null}

        {ended.specialization ? (
          <p className="text-xs text-muted-foreground">
            <span className="font-semibold text-neutral-badge-fg">
              {ENDED_CARD_LABELS.specialization}:{" "}
            </span>
            {ended.specialization}
          </p>
        ) : null}
      </div>

      <EndedApplicationCardActions ended={ended} />
    </div>
  );
}