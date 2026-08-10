import ProfileInformationContainer from "@/features/auth/components/profile-information/ProfileInformationContainer";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Profile information",
};

export default function ProfileInformationPage() {
  return <ProfileInformationContainer />;
}