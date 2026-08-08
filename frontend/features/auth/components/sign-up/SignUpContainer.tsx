import Link from "next/link";

import { AuthCard, SignUpForm } from "@/features/auth";

export default function SignUpContainer() {
  return (
    <AuthCard
      title="Create your account"
      description="Choose your path — students train, companies hire."
      footer={
        <p className="text-sm text-muted-foreground">
          Already have an account?{" "}
          <Link
            href="/sign-in"
            className="font-medium text-secondary-text hover:underline"
          >
            Sign in
          </Link>
        </p>
      }
    >
      <SignUpForm />
    </AuthCard>
  );
}
