import type { Metadata } from "next";

import { MyListingsListContainer } from "@/features/listings";

export const metadata: Metadata = {
  title: "My Listings",
};

export default function Page() {
  return (
    <div className="min-h-[calc(100dvh-3.5rem)] bg-[#FAF7F1] p-8">
      <h1 className="font-sans text-xl font-semibold text-foreground">My Listings</h1>
      <div className="mt-6">
        <MyListingsListContainer />
      </div>
    </div>
  );
}
