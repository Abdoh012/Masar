// Public surface for the "sidebar" feature (contract §1). Signed-in layouts
// import only `Sidebar` from here; SidebarClientShell, the internal
// Collapse/Trigger controls, and all section parts stay internal (research
// R-7/R-8) — no deep imports into components/** from outside the feature.
export { Sidebar } from "./components/sidebar/Sidebar";