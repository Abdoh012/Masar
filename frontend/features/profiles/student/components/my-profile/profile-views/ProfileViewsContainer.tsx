import { notFound } from "next/navigation";

import { fetchProfileViews } from "../../../api";
import { normalizeProfileViews } from "../../../lib/normalize";
import type { ProfileViewsData } from "../../../types";
import { ProfileViewsPanel } from "./ProfileViewsPanel";

export default async function ProfileViewsContainer() {
  const result = await fetchProfileViews();

  if (result.status === 404) {
    notFound();
  }

  if (!result.success) {
    throw new Error(result.error ?? "Unable to load your profile views.");
  }

  const { count, viewers }: ProfileViewsData = normalizeProfileViews(
    result.data,
  );

  return <ProfileViewsPanel count={count} viewers={viewers} />;
}
