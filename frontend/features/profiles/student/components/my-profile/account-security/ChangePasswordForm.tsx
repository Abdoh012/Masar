"use client";

import { useEffect, useState } from "react";
import { useRouter } from "next/navigation";

import { useFormFeedBack } from "@/shared/hooks/useFormFeedback";

import { changePassword } from "../../../actions";
import { AccountSubmitButton } from "./AccountSubmitButton";
import {
  ACCOUNT_SECURITY_LABELS,
  PASSWORD_DIGIT_REGEX,
  PASSWORD_LOWERCASE_REGEX,
  PASSWORD_MIN_LENGTH,
  PASSWORD_SPECIAL_CHAR_REGEX,
  PASSWORD_UPPERCASE_REGEX,
} from "./constants";
import type { PasswordRuleId } from "./constants";
import { PasswordField } from "./PasswordField";
import { PasswordRules } from "./PasswordRules";

export function ChangePasswordForm() {
  const router = useRouter();
  const { formAction, state } = useFormFeedBack(changePassword, null);

  const [currentPassword, setCurrentPassword] = useState("");
  const [newPassword, setNewPassword] = useState("");
  const [confirmPassword, setConfirmPassword] = useState("");

  const isMismatch =
    confirmPassword.length > 0 && confirmPassword !== newPassword;

  const met: Record<PasswordRuleId, boolean> = {
    length: newPassword.length >= PASSWORD_MIN_LENGTH,
    case:
      PASSWORD_UPPERCASE_REGEX.test(newPassword) &&
      PASSWORD_LOWERCASE_REGEX.test(newPassword),
    digit: PASSWORD_DIGIT_REGEX.test(newPassword),
    special: PASSWORD_SPECIAL_CHAR_REGEX.test(newPassword),
    match: confirmPassword.length > 0 && confirmPassword === newPassword,
  };

  // The submit stays disabled until every rule passes and a current password is
  // present; the backend re-validates everything it receives regardless.
  const canSubmit =
    currentPassword.length > 0 && Object.values(met).every(Boolean);

  useEffect(() => {
    if (!state?.success) return;

    setCurrentPassword("");
    setNewPassword("");
    setConfirmPassword("");
    router.replace("/sign-in");
  }, [state?.success, router]);

  return (
    <form action={formAction} className="space-y-4">
      <div className="space-y-3">
        <PasswordField
          id="current-password"
          name="current_password"
          label={ACCOUNT_SECURITY_LABELS.password.currentPasswordLabel}
          value={currentPassword}
          onChange={setCurrentPassword}
          autoComplete="current-password"
        />

        <PasswordField
          id="new-password"
          name="new_password"
          label={ACCOUNT_SECURITY_LABELS.password.newPasswordLabel}
          value={newPassword}
          onChange={setNewPassword}
          autoComplete="new-password"
        />

        <PasswordField
          id="confirm-password"
          name="new_password_confirmation"
          label={ACCOUNT_SECURITY_LABELS.password.confirmPasswordLabel}
          value={confirmPassword}
          onChange={setConfirmPassword}
          autoComplete="new-password"
          invalid={isMismatch}
        />
      </div>

      <PasswordRules met={met} />

      <div className="flex justify-end">
        <AccountSubmitButton
          label={ACCOUNT_SECURITY_LABELS.password.save}
          pendingLabel={ACCOUNT_SECURITY_LABELS.password.saving}
          disabled={!canSubmit}
        />
      </div>
    </form>
  );
}
