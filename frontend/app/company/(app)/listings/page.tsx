import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "My Listings",
};

export default function Page() {
  return (
    <div className="p-8">
      <h1 className="font-sans text-xl font-semibold text-navy">My Listings</h1>
    </div>
  );
}
