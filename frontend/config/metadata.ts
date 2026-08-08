import type { Metadata } from "next";

export const siteConfig = {
  name: "Masar",
  tagline: "From training to opportunity",
  description:
    "Masar connects students and fresh graduates with real companies offering training, hands-on experience, and a documented path toward their first job.",
};

export const baseMetadata: Metadata = {
  title: {
    default: siteConfig.name,
    template: `%s | ${siteConfig.name}`,
  },
  description: siteConfig.description,
};
