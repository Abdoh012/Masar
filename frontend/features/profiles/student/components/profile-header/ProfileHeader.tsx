import { DashboardSection } from "@/shared/components/dashboard-section/DashboardSection";

import type { ProfileHeaderStudent } from "../../types";
import { ProfileCompletionPrompt } from "./ProfileCompletionPrompt";

// The dashboard's identity hero: the student's name, their academic line, and
// the completion nudge. Fed by the dashboard orchestrator from the API's
// `student` block, so nothing here is a mock.
export function ProfileHeader({ student }: { student: ProfileHeaderStudent }) {
  return (
    <DashboardSection
      elevation="hero"
      // The two-column row deliberately starts at `lg`, not `sm`. The nudge
      // beside it has hard floors of its own — the meter's `min-w-44` and the
      // copy's `max-w-xs` cap it around 320px, and it is `shrink-0` — so at
      // 640–1023px a row could not hold both sides and pushed the card wider
      // than its column. Below `lg` the two stack, which has room to breathe.
      className="gap-5 lg:flex-row lg:items-center lg:justify-between lg:gap-8"
    >
      {/* `min-w-0` is what lets a long name truncate instead of pushing the
          card wide. The inner text block has it too, but the inner block is a
          flex child of *this* wrapper — this wrapper is the flex item, and
          without its own `min-w-0` its automatic minimum size is still the
          full name + field + studies widths. */}
      <div className="flex min-w-0 items-center gap-5">
        <span className="flex size-20 shrink-0 items-center justify-center rounded-full bg-linear-to-br from-primary-400 to-primary-700 text-lg font-semibold text-primary-foreground shadow-card-md">
          {student.initials}
        </span>

        <div className="min-w-0">
          <h1 className="truncate text-2xl font-semibold tracking-tight text-primary-text">
            {student.name}
          </h1>

          {student.field ? (
            <p className="mt-0.5 truncate text-sm font-medium text-foreground">
              {student.field}
            </p>
          ) : null}

          {student.studies ? (
            <p className="mt-0.5 truncate text-sm text-muted-foreground">
              {student.studies}
            </p>
          ) : null}
        </div>
      </div>

      {/* The nudge stays on screen at every percentage — it now leads with how far
          along the student is rather than being a bare button. */}
      <ProfileCompletionPrompt percent={student.completion} />
    </DashboardSection>
  );
}
