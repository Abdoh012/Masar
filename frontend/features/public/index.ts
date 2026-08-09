// Public surface for the "public" feature.
// Marketing shell + public pages (landing, about, support, privacy, terms).
// Nothing outside this feature's index should ever be imported by app
// routes (R8) — routes consume only these exports.

export { HomeHero } from "./components/home-hero/HomeHero";
export { HomeFeatures } from "./components/home-features/HomeFeatures";
export { HomeHowItWorks } from "./components/home-how-it-works/HomeHowItWorks";
export { HomeCtaBand } from "./components/home-cta-band/HomeCtaBand";
export { PublicHeader } from "./components/public-header/PublicHeader";
export { PublicFooter } from "./components/public-footer/PublicFooter";
export { AboutContent } from "./components/about-content/AboutContent";
export { PrivacyContent } from "./components/privacy-content/PrivacyContent";
export { TermsContent } from "./components/terms-content/TermsContent";
export { SupportContent } from "./components/support-content/SupportContent";