import { serverFetch } from "@/services/api";
import type { TryCatchResponse } from "@/types/server-action";

export async function fetchProfileHeader(): Promise<TryCatchResponse> {
  return serverFetch({ url: "students/me", cache: "no-store" });
}

export async function fetchFullName(): Promise<TryCatchResponse> {
  return serverFetch({ url: "students/me/full-name", cache: "no-store" });
}

export async function fetchProfileViews(): Promise<TryCatchResponse> {
  return serverFetch({ url: "students/me/profile-views", cache: "no-store" });
}

export async function fetchContactInfo(): Promise<TryCatchResponse> {
  return serverFetch({ url: "students/me/contact", cache: "no-store" });
}

export async function fetchAcademicStatus(): Promise<TryCatchResponse> {
  return serverFetch({
    url: "students/me/academic-status",
    cache: "no-store",
  });
}

export async function fetchStudentSkills(): Promise<TryCatchResponse> {
  return serverFetch({ url: "students/me/skills", cache: "no-store" });
}

export async function fetchEducation(): Promise<TryCatchResponse> {
  return serverFetch({ url: "students/me/education", cache: "no-store" });
}

export async function fetchEducationDegrees(): Promise<TryCatchResponse> {
  return serverFetch({ url: "lookups/education-degrees", cache: "no-store" });
}

export async function fetchStudyFields(): Promise<TryCatchResponse> {
  return serverFetch({ url: "lookups/study-fields", cache: "no-store" });
}

export async function fetchSkillOptions(): Promise<TryCatchResponse> {
  return serverFetch({ url: "lookups/skills", cache: "no-store" });
}
