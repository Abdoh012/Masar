import type { Metadata } from "next";

import { PrivacyContent } from "@/features/public";

export const metadata: Metadata = {
  title: "Privacy Policy",
};

export default function Page() {
  return <PrivacyContent />;
}