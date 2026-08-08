// Placeholder for file uploads (CV uploads, etc.) until the storage
// provider is chosen. Keep the signature stable so features/profiles
// can build against it now.
export async function uploadFile(_file: File): Promise<{ url: string }> {
  throw new Error("services/storage.ts: uploadFile is not implemented yet");
}
