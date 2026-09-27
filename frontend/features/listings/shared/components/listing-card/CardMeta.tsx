import { CalendarClock, CalendarDays, Clock, MapPin } from "lucide-react";

import { FORMAT_LABELS } from "../../lib/constants";

import { CARD_META_DEADLINE_PREFIX, CARD_META_POSTED_PREFIX } from "./constants";

interface CardMetaProps {
  duration?: string;
  format: "in_person" | "remote" | "hybrid";
  /** Optional: a card whose source has no created date (the dashboard's
   *  recommendation presenter can omit one) simply drops the "Posted" line
   *  rather than formatting an invalid Date, which `Intl` throws on. Every
   *  other caller always has one, so their output is unchanged. */
  createdAt?: string;
  deadline?: string;
}

function formatPostedDate(iso: string): string {
  return new Intl.DateTimeFormat("en-US", {
    month: "short",
    day: "numeric",
    timeZone: "UTC",
  }).format(new Date(iso));
}

function formatDeadlineDate(iso: string): string {
  return new Intl.DateTimeFormat("en-US", {
    day: "numeric",
    month: "short",
    year: "numeric",
    timeZone: "UTC",
  }).format(new Date(iso));
}

export function CardMeta({
  duration,
  format,
  createdAt,
  deadline,
}: CardMetaProps) {
  return (
    <div className="flex flex-wrap items-center gap-x-4 gap-y-1.5 text-xs text-muted-foreground">
      {duration ? (
        <span className="flex items-center gap-1.5">
          <Clock className="size-3.5" />
          {duration}
        </span>
      ) : null}

      <span className="flex items-center gap-1.5">
        <MapPin className="size-3.5" />
        {FORMAT_LABELS[format]}
      </span>

      {deadline ? (
        <span className="flex items-center gap-1.5">
          <CalendarClock className="size-3.5" />
          {CARD_META_DEADLINE_PREFIX} {formatDeadlineDate(deadline)}
        </span>
      ) : null}

      {createdAt ? (
        <span className="flex items-center gap-1.5">
          <CalendarDays className="size-3.5" />
          {CARD_META_POSTED_PREFIX} {formatPostedDate(createdAt)}
        </span>
      ) : null}
    </div>
  );
}
