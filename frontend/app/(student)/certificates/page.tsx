import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "My Certificates",
};

export default function Page() {
  return (
    <div className="p-8">
      <h1 className="font-sans text-xl font-semibold text-navy">My Certificates</h1>
    </div>
  );
}
