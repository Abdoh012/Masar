import type { Metadata } from "next";

import { BrowseListingsContainer } from "@/features/listings";

export const metadata: Metadata = {
  title: "Browse Trainings",
};

export default function Page() {
  return (
    <div className="p-8">
      <h1 className="font-sans text-xl font-semibold text-foreground">Browse Trainings</h1>
      <div className="mt-6">
        <BrowseListingsContainer />
      </div>
    </div>
  );
}
