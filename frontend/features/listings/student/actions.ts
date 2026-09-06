"use server";

import { revalidatePath } from "next/cache";

import { serverFetch } from "@/services/api";

export async function saveTrainingAction(id: string) {
  const result = await serverFetch({
    url: `trainings/save/${id}`,
    method: "POST",
  });
  if (result.success) {
    revalidatePath("/listings");
    revalidatePath(`/listings/${id}`);
  }
  return result;
}

export async function unsaveTrainingAction(id: string) {
  const result = await serverFetch({
    url: `trainings/unsave/${id}`,
    method: "DELETE",
  });
  if (result.success) {
    revalidatePath("/listings");
    revalidatePath(`/listings/${id}`);
  }
  return result;
}

export async function getSavedListings() {
  return serverFetch({ url: "trainings/saved/list" });
}