import type { Metadata } from "next";

import { BrowseListingsContainer } from "@/features/listings";

export const metadata: Metadata = {
  title: "Browse Trainings",
};

export default function Page() {
  return (
    <div className="min-h-[calc(100dvh-3.5rem)] bg-[#FAF7F1]">
      <BrowseListingsContainer />
    </div>
  );
}
