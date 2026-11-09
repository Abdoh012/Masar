// Copy + limits for the identity hero card (structure rules §14).
//
// The image rules mirror the backend's PROFILE_IMAGE_* constants: they are
// checked here only to fail fast in the tab, the API remains the source of
// truth.

export const PROFILE_IMAGE_MIME_TYPES = [
  "image/jpeg",
  "image/png",
  "image/webp",
] as const;

export const PROFILE_IMAGE_ACCEPT = PROFILE_IMAGE_MIME_TYPES.join(",");

export const PROFILE_IMAGE_MAX_BYTES = 5 * 1024 * 1024;

export const IDENTITY_LABELS = {
  memberSince: "Member since",
  // Used only when the account has no name set — never as a stand-in for a
  // real name.
  nameFallback: "Your profile",
  profilePhotoAlt: "Profile photo",
  changePhoto: "Change profile photo",
  addPhoto: "Add profile photo",
  changePhotoAction: "Change photo",
  addPhotoAction: "Add photo",
  removePhoto: "Remove",
  uploading: "Uploading profile photo",
  invalidType: "Choose a JPG, PNG or WebP image.",
  tooLarge: "That image is larger than 5 MB.",
  uploadFailed: "Could not upload the profile photo.",
  removeFailed: "Could not remove the profile photo.",
  uploaded: "Profile photo updated.",
  removed: "Profile photo removed.",
} as const;
