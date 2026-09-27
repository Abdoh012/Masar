// Training progress from the training's own dates (pure util — no React, so it
// runs during server render and needs no client state or effect).
//
// The backend sends `starts_at` / `ends_at` as ISO-8601 *with an explicit UTC
// offset* (application_iso8601 renders the Africa/Cairo column through
// DateTime::format('c')), so a winter training reads `...T09:00:00+02:00` and a
// summer one `...T09:00:00+03:00`. `new Date()` therefore resolves both to the
// correct instant regardless of the server's own timezone — the offsets are
// already in the string, so nothing here may re-parse or re-zone them (stripping
// the offset, or building a Date from the wall-clock parts, would shift the
// progress by hours and is exactly the bug this comment exists to prevent).

export type TrainingProgressState =
  /** Now is before starts_at — 0%. */
  | "not_started"
  /** Now is between the two dates. */
  | "in_progress"
  /** Now is at or after ends_at — 100%. */
  | "completed";

export interface TrainingProgress {
  /** Elapsed share of the run, 0..100, rounded to a whole percent. Null when
   *  the dates can't produce a meaningful bar (see below). */
  percent: number | null;
  state: TrainingProgressState;
}

export interface TrainingProgressInput {
  startsAt: string | null;
  endsAt: string | null;
}

/** Whether the run has begun / finished. Decided from the dates so the label
 *  can never contradict the bar sitting next to it. */
function resolveState(
  now: number,
  start: number,
  end: number,
): TrainingProgressState {
  if (now < start) return "not_started";
  if (now >= end) return "completed";
  return "in_progress";
}

/** Progress of an accepted training, from its own start/end dates.
 *
 *  percent = clamp(((now - start) / (end - start)) * 100, 0, 100)
 *
 *  `remainingDays` is deliberately NOT an input: the backend's value is a
 *  calendar-day count (days from *today's midnight* to ends_at), not an
 *  instant-based elapsed share, so folding it into the percentage would create
 *  a second source of truth that disagrees with the bar by up to a day. It stays
 *  what it is — the countdown the card states out loud — while this function
 *  owns the bar.
 *
 *  Returns percent: null (the caller hides the track) when either date is
 *  missing or unparseable, or when the two are equal — the zero-duration case,
 *  where the division has no denominator. `state` is still resolved there, so
 *  the surrounding copy stays correct even with no bar. */
export function calculateTrainingProgress({
  startsAt,
  endsAt,
}: TrainingProgressInput): TrainingProgress {
  const start = startsAt ? new Date(startsAt).getTime() : Number.NaN;
  const end = endsAt ? new Date(endsAt).getTime() : Number.NaN;

  if (Number.isNaN(start) || Number.isNaN(end)) {
    // No usable dates: the state is whatever the one known date implies, and
    // the bar is omitted rather than guessed at.
    if (!Number.isNaN(start)) {
      return {
        percent: null,
        state: Date.now() < start ? "not_started" : "in_progress",
      };
    }
    if (!Number.isNaN(end)) {
      return {
        percent: null,
        state: Date.now() >= end ? "completed" : "in_progress",
      };
    }
    return { percent: null, state: "in_progress" };
  }

  // Zero-duration run (starts_at === ends_at), or an inverted pair: there is
  // nothing to be a fraction *of*, so the division has no denominator. Resolve
  // it against `now` instead of defaulting to "finished" — a same-instant run
  // in the future has demonstrably not happened yet, and saying otherwise
  // would show a full bar for a training that hasn't begun.
  if (end <= start) {
    return Date.now() < start
      ? { percent: 0, state: "not_started" }
      : { percent: 100, state: "completed" };
  }

  const now = Date.now();
  const state = resolveState(now, start, end);

  if (state === "not_started") return { percent: 0, state };
  if (state === "completed") return { percent: 100, state };

  const elapsed = now - start;
  const total = end - start;
  const percent = Math.min(100, Math.max(0, (elapsed / total) * 100));

  return { percent: Math.round(percent), state };
}
