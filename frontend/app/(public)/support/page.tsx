import type { Metadata } from "next";

import { SupportContent } from "@/features/public";

export const metadata: Metadata = {
  title: "Support",
};

export default function Page() {
  return <SupportContent />;
}