import type { Metadata } from "next";

import { HomeCtaBand, HomeFeatures, HomeHero, HomeHowItWorks } from "@/features/public";

export const metadata: Metadata = {
  title: "Home",
};

export default function Page() {
  return (
    <>
      <HomeHero />
      <HomeFeatures />
      <HomeHowItWorks />
      <HomeCtaBand />
    </>
  );
}