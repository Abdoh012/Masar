// Public surface for the "dashboard" feature.
// The per-role landing surface each role sees after sign-in.
//
// Only export what routes are meant to consume. Nothing outside this feature
// should ever import from a deeper path than this file (R8).

export { default as StudentDashboard } from "./student/components/dashboard/DashboardContainer";
