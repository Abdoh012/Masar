import type { Metadata } from "next";

import { AboutContent } from "@/features/public";

export const metadata: Metadata = {
  title: "About",
};

export default function Page() {
  return <AboutContent />;
}