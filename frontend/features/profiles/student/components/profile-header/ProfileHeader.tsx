import { DashboardSection } from "@/shared/components/dashboard-section/DashboardSection";

import { PROFILE } from "./constants";
import { ProfileCompletionPrompt } from "./ProfileCompletionPrompt";

export function ProfileHeader() {
  const { name, field, initials, studies } = PROFILE;

  return (
    <DashboardSection
      elevation="hero"
      className="gap-5 sm:flex-row sm:items-center sm:justify-between sm:gap-8"
    >
      <div className="flex items-center gap-5">
        {/* TODO: Change this to a real image */}
        <span className="flex size-14 shrink-0 items-center justify-center rounded-full bg-linear-to-br from-primary-400 to-primary-700 text-lg font-semibold text-primary-foreground shadow-card-md">
          {initials}
        </span>

        <div className="min-w-0">
          {/* TODO: Change this to a real name */}
          <h1 className="truncate text-2xl font-semibold tracking-tight text-primary-text">
            {name}
          </h1>

          {/* TODO: Change this to a real field */}
          <p className="mt-0.5 truncate text-sm font-medium text-foreground">
            {field}
          </p>

          {/* TODO: Change this to a real studies */}
          {studies ? (
            <p className="mt-0.5 truncate text-sm text-muted-foreground">
              {studies}
            </p>
          ) : null}
        </div>
      </div>

      {/* Complete Profile — unconditionally shown for now; the isComplete flag
          is read from PROFILE but the gate is still a hardcoded `true` (see the
          unused PROFILE_INCOMPLETE variant). Behaviour is deliberately unchanged. */}
      {true ? <ProfileCompletionPrompt /> : null}
    </DashboardSection>
  );
}
