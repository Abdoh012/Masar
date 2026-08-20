"use client";

import { useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import { Loader2 } from "lucide-react";

import { PASSWORD_UPDATED_COPY, REDIRECT_SECONDS } from "./constants";

// RedirectCountdown: owns the post-reset waiting state — shows the redirect
// info with a live countdown, then pushes to /sign-in once REDIRECT_SECONDS
// elapse.
export function RedirectCountdown() {
  const router = useRouter();
  const [secondsLeft, setSecondsLeft] = useState(REDIRECT_SECONDS);

  useEffect(() => {
    const interval = setInterval(() => {
      setSecondsLeft((s) => Math.max(0, s - 1));
    }, 1000);
    return () => clearInterval(interval);
  }, []);

  useEffect(() => {
    if (secondsLeft === 0) router.push("/sign-in");
  }, [secondsLeft, router]);

  return (
    <div className="flex flex-col items-center gap-3 text-center">
      <Loader2 className="size-6 animate-spin text-secondary-text" />
      <p className="text-sm text-muted-foreground">
        {PASSWORD_UPDATED_COPY.redirectPrefix} {secondsLeft}s...
      </p>
    </div>
  );
}
