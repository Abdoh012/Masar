import type { Metadata } from "next";

import { BrowseListingsContainer } from "@/features/listings";

export const metadata: Metadata = {
  title: "Browse Trainings",
};

export default function Page() {
  return <BrowseListingsContainer />;
}
