// Public surface for the "chat" feature.
// In-app thread, unlocked automatically the moment a company accepts an application.
//
// Only export what other parts of the app (routes, other features via
// shared/) are meant to consume. Nothing outside this feature should ever
// import from a deeper path than this file (R8). Features never import
// from each other directly — promote to top-level shared/ on second use (R7).
