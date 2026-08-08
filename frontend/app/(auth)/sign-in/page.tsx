import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Sign In",
};

export default function Page() {
  return (
    <div className="p-8">
      <h1 className="text-xl font-semibold text-navy">Sign In</h1>
    </div>
  );
}
