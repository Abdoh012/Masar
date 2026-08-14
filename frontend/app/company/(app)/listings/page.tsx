import type { Metadata } from "next";

import { MyListingsList } from "@/features/listings";

export const metadata: Metadata = {
  title: "My Listings",
};

export default function Page() {
  return (
    <div className="p-8">
      <h1 className="font-sans text-xl font-semibold text-foreground">My Listings</h1>
      <div className="mt-6">
        <MyListingsList />
      </div>
    </div>
  );
}
