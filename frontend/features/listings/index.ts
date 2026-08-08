// Public surface for the "listings" feature.
// Two-way discovery: student browse-by-field, company post/edit, admin moderation.
//
// Only export what other parts of the app (routes, other features via
// shared/) are meant to consume. Nothing outside this feature should ever
// import from a deeper path than this file (R8). Features never import
// from each other directly — promote to top-level shared/ on second use (R7).
