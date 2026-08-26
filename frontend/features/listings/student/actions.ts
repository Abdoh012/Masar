"use server";

import { serverFetch } from "@/services/api";

export async function saveTrainingAction(id: string) {
  return serverFetch({ url: `trainings/save/${id}`, method: "POST" });
}

export async function unsaveTrainingAction(id: string) {
  return serverFetch({ url: `trainings/unsave/${id}`, method: "DELETE" });
}

export async function getSavedListings() {
  return serverFetch({ url: "trainings/saved/list" });
}
