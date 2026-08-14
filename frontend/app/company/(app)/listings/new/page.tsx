import type { Metadata } from "next";

import { ListingFormContainer } from "@/features/listings";

export const metadata: Metadata = {
  title: "New Listing",
};

export default function Page() {
  return (
    <div className="p-8">
      <h1 className="font-sans text-xl font-semibold text-foreground">New Listing</h1>
      <div className="mt-6 max-w-2xl">
        <ListingFormContainer mode="create" />
      </div>
    </div>
  );
}
