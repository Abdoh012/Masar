"use client";

import { useState } from "react";
import { AtSign, KeyRound, ShieldCheck } from "lucide-react";

import { Button } from "@/shared/components/ui/button";

import { ProfileSection } from "../ProfileSection";
import { ProfileSubPanel } from "../ProfileSubPanel";
import { PasswordField } from "./PasswordField";
import { PasswordRules } from "./PasswordRules";
import { UsernameField } from "./UsernameField";
import { ACCOUNT_SECURITY_LABELS, PASSWORD_MIN_LENGTH } from "./constants";

// AccountSecurityContainer: the two credential forms side by side (username,
// password) inside the shared section shell. It owns the two password values
// because the confirm field's validity depends on the password field's current
// value — the per-field controlled exception in structure rules §10, not a
// reason to control the username form too. The password Save button is disabled
// until both live checks pass, and is otherwise inert: no profile API exists, so
// it must not fake a request, a spinner, or a success toast.
export default function AccountSecurityContainer() {
  const [password, setPassword] = useState("");
  const [confirmPassword, setConfirmPassword] = useState("");

  const isMinLengthMet = password.length >= PASSWORD_MIN_LENGTH;
  // Only nag once both fields have something in them, so the untouched form
  // doesn't open with a red "they don't match".
  const isMismatch =
    confirmPassword.length > 0 && confirmPassword !== password;
  const isPasswordValid = isMinLengthMet && confirmPassword === password;

  return (
    <ProfileSection
      icon={ShieldCheck}
      title={ACCOUNT_SECURITY_LABELS.title}
      description={ACCOUNT_SECURITY_LABELS.description}
    >
      <div className="grid gap-4 lg:grid-cols-2">
        <ProfileSubPanel
          icon={AtSign}
          title={ACCOUNT_SECURITY_LABELS.username.title}
          description={ACCOUNT_SECURITY_LABELS.username.description}
        >
          <UsernameField
            defaultValue={ACCOUNT_SECURITY_LABELS.username.defaultValue}
          />
        </ProfileSubPanel>

        <ProfileSubPanel
          icon={KeyRound}
          title={ACCOUNT_SECURITY_LABELS.password.title}
          description={ACCOUNT_SECURITY_LABELS.password.description}
        >
          <form
            className="space-y-4"
            onSubmit={(event) => {
              // TODO: connect to backend — hand these to the updatePassword
              // server action (student/actions.ts); the backend owns the real
              // password policy, this pair of checks is UX only.
              event.preventDefault();
            }}
          >
            <div className="space-y-3">
              <PasswordField
                id="new-password"
                label={ACCOUNT_SECURITY_LABELS.password.newPasswordLabel}
                value={password}
                onChange={setPassword}
              />

              <PasswordField
                id="confirm-password"
                label={ACCOUNT_SECURITY_LABELS.password.confirmPasswordLabel}
                value={confirmPassword}
                onChange={setConfirmPassword}
                invalid={isMismatch}
              />
            </div>

            <PasswordRules
              minLengthMet={isMinLengthMet}
              matches={!isMismatch && confirmPassword.length > 0}
            />

            <div className="flex justify-end">
              <Button
                type="submit"
                size="sm"
                disabled={!isPasswordValid}
                className="cursor-pointer"
              >
                {ACCOUNT_SECURITY_LABELS.password.save}
              </Button>
            </div>
          </form>
        </ProfileSubPanel>
      </div>
    </ProfileSection>
  );
}
