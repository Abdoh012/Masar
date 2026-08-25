import type { Metadata } from "next";
import { Suspense } from "react";

import { BrowseListingsContainer } from "@/features/listings";

export const metadata: Metadata = {
  title: "Browse Trainings",
};

export default function Page() {
  return (
    <Suspense>
      <BrowseListingsContainer />
    </Suspense>
  );
}
