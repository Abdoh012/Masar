import Motion from "@/shared/components/animation/components/Motion";
import { fadeInUp } from "@/shared/lib/animations";
import { PROFILE } from "./constants";
import { ProfileCompletionPrompt } from "./ProfileCompletionPrompt";

export function ProfileHeader() {
  const { name, field, initials, isComplete, studies } = PROFILE;

  return (
    <Motion
      variants={fadeInUp}
      initial="hidden"
      whileInView="visible"
      viewport={{ once: true, margin: "-40px" }}
      transition={{ duration: 0.24, ease: "easeOut" }}
      className="flex w-full flex-col gap-4 rounded-2xl border border-border bg-card p-5 shadow-card sm:flex-row sm:items-center sm:justify-between"
    >
      <div className="flex items-center gap-4">
        {/* TODO: Change this to a real image */}
        <span className="flex size-11 shrink-0 items-center justify-center rounded-full bg-primary-tint font-semibold text-primary-text">
          {initials}
        </span>

        <div className="min-w-0">
          {/* TODO: Change this to a real name */}
          <h1 className="truncate text-lg font-semibold text-primary-text sm:text-xl">
            {name}
          </h1>

          {/* TODO: Change this to a real field */}
          <p className="truncate text-sm text-muted-foreground">{field}</p>

          {/* TODO: Change this to a real studies */}
          {studies ? (
            <p className="mt-0.5 truncate text-xs text-muted-foreground">
              {studies}
            </p>
          ) : null}
        </div>
      </div>

      {/* Complete Profile */}
      {true ? <ProfileCompletionPrompt /> : null}
    </Motion>
  );
}
