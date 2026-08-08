import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "About",
};

export default function Page() {
  return (
    <div className="p-8">
      <h1 className="font-sans text-xl font-semibold text-navy">About</h1>
    </div>
  );
}
