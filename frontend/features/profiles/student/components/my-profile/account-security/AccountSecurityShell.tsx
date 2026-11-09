"use client";

import { KeyRound, ShieldCheck, UserRound } from "lucide-react";

import { ProfileSection } from "../ProfileSection";
import { ProfileSubPanel } from "../ProfileSubPanel";
import { ACCOUNT_SECURITY_LABELS } from "./constants";
import { ChangePasswordForm } from "./ChangePasswordForm";
import { FullNameForm } from "./FullNameForm";

interface AccountSecurityShellProps {
  fullName: string | null;
}

export function AccountSecurityShell({ fullName }: AccountSecurityShellProps) {
  return (
    <ProfileSection
      icon={<ShieldCheck className="size-4" />}
      title={ACCOUNT_SECURITY_LABELS.title}
      description={ACCOUNT_SECURITY_LABELS.description}
    >
      <div className="grid gap-4 lg:grid-cols-2">
        <ProfileSubPanel
          icon={<UserRound className="size-4" />}
          title={ACCOUNT_SECURITY_LABELS.fullName.title}
          description={ACCOUNT_SECURITY_LABELS.fullName.description}
        >
          <FullNameForm fullName={fullName} />
        </ProfileSubPanel>

        <ProfileSubPanel
          icon={<KeyRound className="size-4" />}
          title={ACCOUNT_SECURITY_LABELS.password.title}
          description={ACCOUNT_SECURITY_LABELS.password.description}
        >
          <ChangePasswordForm />
        </ProfileSubPanel>
      </div>
    </ProfileSection>
  );
}
