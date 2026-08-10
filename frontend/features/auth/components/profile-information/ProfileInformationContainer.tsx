import Link from "next/link";

import { AuthCard, ProfileInformationForm } from "@/features/auth";

export default function ProfileInformationContainer() {
  return (
    <AuthCard
      title="Complete your profile"
      description="Tell companies who you are — your field, specialization, and university."
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
      <ProfileInformationForm />
    </AuthCard>
  );
}