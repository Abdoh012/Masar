// Student-scoped server actions for the certificates feature.
// The page's reads go through student/api.ts into the async server
// CertificatePageContent orchestrator — no client-side read actions needed.
// Mutations (the request-certificate flow) will land here when the backend
// /certificates POST is wired.