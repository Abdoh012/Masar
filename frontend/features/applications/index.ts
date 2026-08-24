// Public surface for the "applications" feature.
// Apply / withdraw / accept-reject-with-reason / auto-reject-on-close.
//
// Only export what other parts of the app (routes, other features via
// shared/) are meant to consume. Nothing outside this feature should ever
// import from a deeper path than this file (R8). Features never import
// from each other directly — promote to top-level shared/ on second use (R7).

export { ActiveTraining } from "./student/components/active-training/ActiveTraining";
export { ApplicationsSnapshot } from "./student/components/applications-snapshot/ApplicationsSnapshot";
export { MyApplicationsPage } from "./student/components/my-applications/MyApplicationsPage";
export { TrainingApplicationContainer } from "./student/components/training-application/TrainingApplicationContainer";
