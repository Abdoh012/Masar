import Link from "next/link";

import { AuthCard, SignInForm } from "@/features/auth";
import { SessionExpiredToast } from "./SessionExpiredToast";

interface SignInContainerProps {
  sessionExpired: boolean;
  error: string;
}

// SignInContainer: renders the sign-in card. When the user lands here via the
// session-expired redirect (an unrecoverable 401 in serverFetch), the
// SessionExpiredToast leaf surfaces the backend error as a toast.
export default function SignInContainer({
  sessionExpired,
  error,
}: SignInContainerProps) {
  return (
    <AuthCard
      title="Welcome back"
      description="Sign in to continue to Masar."
      footer={
        <p className="text-sm text-muted-foreground">
          New to Masar?{" "}
          <Link
            href="/sign-up"
            className="font-medium text-secondary-text hover:underline"
          >
            Create an account
          </Link>
        </p>
      }
    >
      <SessionExpiredToast sessionExpired={sessionExpired} error={error} />
      <SignInForm />
    </AuthCard>
  );
}