import type { ReactNode } from "react";
import type { Metadata } from "next";
import {
  IBM_Plex_Sans,
  IBM_Plex_Serif,
  IBM_Plex_Mono,
  IBM_Plex_Sans_Arabic,
} from "next/font/google";
import { baseMetadata } from "../config/metadata";
import "./globals.css";

// UI / body text
const ibmPlexSans = IBM_Plex_Sans({
  subsets: ["latin"],
  weight: ["300", "400", "500", "600", "700"],
  style: ["normal", "italic"],
  variable: "--font-ibm-plex-sans",
  display: "swap",
});

// Certificate template + formal headline text
const ibmPlexSerif = IBM_Plex_Serif({
  subsets: ["latin"],
  weight: ["400", "500", "600"],
  style: ["normal", "italic"],
  variable: "--font-ibm-plex-serif",
  display: "swap",
});

// Certificate IDs, dates, reference numbers
const ibmPlexMono = IBM_Plex_Mono({
  subsets: ["latin"],
  weight: ["400", "500", "600"],
  variable: "--font-ibm-plex-mono",
  display: "swap",
});

// Wired now so v2 Arabic UI is a locale flip, not a rebuild
const ibmPlexSansArabic = IBM_Plex_Sans_Arabic({
  subsets: ["arabic"],
  weight: ["300", "400", "500", "600", "700"],
  variable: "--font-ibm-plex-sans-arabic",
  display: "swap",
});

export const metadata: Metadata = baseMetadata;

export default function RootLayout({ children }: { children: ReactNode }) {
  return (
    <html
      lang="en"
      className={[
        ibmPlexSans.variable,
        ibmPlexSerif.variable,
        ibmPlexMono.variable,
        ibmPlexSansArabic.variable,
      ].join(" ")}
    >
      <body>{children}</body>
    </html>
  );
}
