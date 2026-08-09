import type { Metadata } from "next";

import { TermsContent } from "@/features/public";

export const metadata: Metadata = {
  title: "Terms of Service",
};

export default function Page() {
  return <TermsContent />;
}