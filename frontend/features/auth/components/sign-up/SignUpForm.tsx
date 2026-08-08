"use client";

import { useState } from "react";

import { FormField } from "../shared/FormField";
import { SubmitButton } from "../shared/SubmitButton";
import { RoleSelector } from "./role-selector/RoleSelector";
import { FIELD_CONFIG } from "../../lib/constants";
import Footer from "./footer/Footer";

export function SignUpForm() {
  const [role, setRole] = useState<"student" | "company">("student");
  const isCompany = role === "company";

  return (
    <form className="space-y-5">
      <RoleSelector value={role} onChange={setRole} />
      <input type="hidden" name="role" value={role} />

      <FormField
        name="fullName"
        label={FIELD_CONFIG.fullName.label}
        type={FIELD_CONFIG.fullName.type}
        placeholder={FIELD_CONFIG.fullName.placeholder}
      />

      <FormField
        name="email"
        label={FIELD_CONFIG.email.label}
        type={FIELD_CONFIG.email.type}
        placeholder={FIELD_CONFIG.email.placeholder}
      />

      <FormField
        name="password"
        type={FIELD_CONFIG.password.type}
        label={FIELD_CONFIG.password.label}
        placeholder={FIELD_CONFIG.password.placeholder}
      />

      {isCompany ? (
        <FormField
          name="companyName"
          label={FIELD_CONFIG.companyName.label}
          type={FIELD_CONFIG.companyName.type}
          placeholder={FIELD_CONFIG.companyName.placeholder}
        />
      ) : null}

      <Footer />

      <SubmitButton>Create account</SubmitButton>
    </form>
  );
}
