import type { Metadata } from "next";

import { BrowseListingsContainer } from "@/features/listings";

export const metadata: Metadata = {
  title: "Browse Trainings",
};

interface PageProps {
  searchParams: Promise<Record<string, string | string[] | undefined>>;
}

export default async function Page({ searchParams }: PageProps) {
  const sp = await searchParams;

  return <BrowseListingsContainer searchParams={sp} />;
}