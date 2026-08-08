"use client";

import { SIGN_UP_ROLES } from "../../../lib/constants";

import Role from "./Role";
import { RoleType } from "@/features/auth/lib/constants";

interface RoleSelectorProps {
  value: RoleType;
  onChange: (role: RoleType) => void;
}

export function RoleSelector({ value, onChange }: RoleSelectorProps) {
  return (
    <div className="grid grid-cols-2 gap-3">
      {SIGN_UP_ROLES.map((role) => {
        return (
          <Role
            key={role.value}
            value={role.value}
            activeRole={value}
            onChange={onChange}
            Icon={role.icon}
            label={role.label}
            description={role.description}
          />
        );
      })}
    </div>
  );
}
