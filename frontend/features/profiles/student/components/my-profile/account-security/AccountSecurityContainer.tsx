import { notFound } from "next/navigation";

import { fetchFullName } from "../../../api";
import { normalizeFullName } from "../../../lib/normalize";
import { AccountSecurityShell } from "./AccountSecurityShell";

export default async function AccountSecurityContainer() {
  const result = await fetchFullName();

  if (result.status === 404) {
    notFound();
  }

  if (!result.success) {
    throw new Error(result.error ?? "Unable to load your account details.");
  }

  return <AccountSecurityShell fullName={normalizeFullName(result.data)} />;
}
