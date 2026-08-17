"use client";

import Link from "next/link";
import { useEffect } from "react";
import { useRouter } from "next/navigation";

import { AuthCard, ProfileInformationForm } from "@/features/auth";
import { useSignupDraft } from "../../shared/hooks/useSignupDraft";

export default function ProfileInformationContainer() {
  const { draft } = useSignupDraft();

  const router = useRouter();

  useEffect(() => {
    if (!draft) router.replace("/sign-up");
  }, [draft, router]);

  if (!draft) return null;

  const isCompany = draft.role === "company";

  return (
    <AuthCard
      title="Complete your profile"
      description={
        isCompany
          ? "Tell us about your company — your industry and what you offer trainees."
          : "Tell companies who you are — your field, specialization, and university."
      }
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
      <ProfileInformationForm draft={draft} />
    </AuthCard>
  );
}
