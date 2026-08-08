import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Messages",
};

export default function Page() {
  return (
    <div className="p-8">
      <h1 className="font-sans text-xl font-semibold text-navy">Messages</h1>
    </div>
  );
}
