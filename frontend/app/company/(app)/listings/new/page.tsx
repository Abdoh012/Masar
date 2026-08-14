import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "New Listing",
};

export default function Page() {
  return (
    <div className="p-8">
      <h1 className="font-sans text-xl font-semibold text-navy">New Listing</h1>
    </div>
  );
}
