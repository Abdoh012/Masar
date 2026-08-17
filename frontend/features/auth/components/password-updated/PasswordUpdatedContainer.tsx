import { AuthCard } from "@/features/auth";

import { RedirectCountdown } from "./RedirectCountdown";
import { SuccessVisual } from "./SuccessVisual";
import { PASSWORD_UPDATED_COPY } from "./constants";

// PasswordUpdatedContainer: composes the post-reset landing card — success
// visual, confirmation copy, and the client countdown that auto-redirects to
// /sign-in after REDIRECT_SECONDS.
export default function PasswordUpdatedContainer() {
  return (
    <AuthCard
      title={PASSWORD_UPDATED_COPY.heading}
      description={PASSWORD_UPDATED_COPY.description}
    >
      <div className="flex flex-col items-center gap-5 text-center">
        <SuccessVisual />
        <p className="text-sm leading-relaxed text-muted-foreground">
          {PASSWORD_UPDATED_COPY.info}
        </p>
        <RedirectCountdown />
      </div>
    </AuthCard>
  );
}
