import { notFound } from "next/navigation";

import { fetchContactInfo } from "../../../api";
import { normalizeContactInfo } from "../../../lib/normalize";
import { ContactInfoPanel } from "./ContactInfoPanel";

export default async function ContactInfoContainer() {
  const result = await fetchContactInfo();

  if (result.status === 404) {
    notFound();
  }

  if (!result.success) {
    throw new Error(result.error ?? "Unable to load your contact info.");
  }

  return <ContactInfoPanel contact={normalizeContactInfo(result.data)} />;
}
