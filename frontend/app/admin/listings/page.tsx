import type { Metadata } from "next";

import { ModerateListingsTableContainer } from "@/features/listings";

export const metadata: Metadata = {
  title: "Moderate Listings",
};

export default function Page() {
  return (
    <div className="min-h-[calc(100dvh-3.5rem)] bg-[#FAF7F1] p-8">
      <h1 className="font-sans text-xl font-semibold text-foreground">Moderate Listings</h1>
      <div className="mt-6">
        <ModerateListingsTableContainer />
      </div>
    </div>
  );
}
