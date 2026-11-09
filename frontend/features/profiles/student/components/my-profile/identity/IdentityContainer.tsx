import { fetchProfileHeader } from "../../../api";
import { normalizeProfileIdentity } from "../../../lib/normalize";
import { IdentityHero } from "./IdentityHero";

export default async function IdentityContainer() {
  const result = await fetchProfileHeader();

  if (!result.success) {
    throw new Error(result.error ?? "Unable to load your profile header.");
  }

  return <IdentityHero profile={normalizeProfileIdentity(result)} />;
}
