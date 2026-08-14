import type { Metadata } from "next";

import { ModerateListingsTable } from "@/features/listings";

export const metadata: Metadata = {
  title: "Moderate Listings",
};

export default function Page() {
  return (
    <div className="p-8">
      <h1 className="font-sans text-xl font-semibold text-foreground">Moderate Listings</h1>
      <div className="mt-6">
        <ModerateListingsTable />
      </div>
    </div>
  );
}
