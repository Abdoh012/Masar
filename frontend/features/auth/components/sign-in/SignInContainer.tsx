import Link from "next/link";

import { AuthCard, SignInForm } from "@/features/auth";

export default function SignInContainer() {
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
      <SignInForm />
    </AuthCard>
  );
}