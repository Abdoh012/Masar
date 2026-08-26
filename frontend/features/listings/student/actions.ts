"use server";

import { serverFetch } from "@/services/api";

export async function saveTrainingAction(id: string) {
  return serverFetch({ url: `trainings/save/${id}` });
}

export async function unsaveTrainingAction(id: string) {
  return serverFetch({ url: `trainings/unsave/${id}` });
}
