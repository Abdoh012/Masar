// API → UI mapping for the My Applications page (role-level normalize,
// mirroring features/listings/student/lib/normalize.ts). Raw backend card DTOs
// (backend docs §1.1–§1.5) are coerced here once, keeping the container and
// card leaves free of API-shape knowledge.
import {
  ApplicationStatus,
  BankAccount,
  MyApplication,
} from "@/features/applications/student/types";

type UnknownRecord = Record<string, unknown>;

const toStr = (value: unknown): string | undefined =>
  typeof value === "string" && value.length > 0 ? value : undefined;
const toBool = (value: unknown): boolean => value === true || value === 1;
const toNum = (value: unknown): number | null =>
  typeof value === "number" ? value : null;

function normalizeBankAccount(raw: unknown): BankAccount | null {
  if (!raw || typeof raw !== "object") return null;
  const account = raw as UnknownRecord;
  if (typeof account.account_number !== "string") return null;
  return {
    bankName: toStr(account.bank_name) ?? null,
    accountName: toStr(account.account_holder_name) ?? null,
    accountNumber: account.account_number,
    instructions: toStr(account.instructions) ?? null,
  };
}

/** Accepted cards carry the trial as flat nullable fields on the card:
 *  free_trial_days (the plan it) + free_trial_days_remaining (counts down once
 *  the training starts, floors at 0; null for free trainings and while no
 *  starts_at is set). */
function normalizeTrial(
  rawDays: unknown,
  rawRemaining: unknown,
): MyApplication["trial"] {
  const days = toNum(rawDays);
  const daysRemaining = toNum(rawRemaining);
  if (days === null && daysRemaining === null) return undefined;
  return { days, daysRemaining };
}

/** Maps one backend application card to the UI MyApplication shape. Fields the
 *  API does not send for a status (e.g. canWithdraw on Accepted) stay
 *  undefined, so leaves can branch on presence. */
export function normalizeApplicationItem(raw: unknown): MyApplication {
  const card = (raw ?? {}) as UnknownRecord;

  const item: MyApplication = {
    id: Number(card.id ?? 0),
    trainingId: Number(card.training_id ?? 0),
    listingTitle: toStr(card.training_title) ?? "Training",
    companyName: toStr(card.company_name) ?? "Company",
    companyLogo: toStr(card.company_logo) ?? null,
    status: (toStr(card.status) as ApplicationStatus) ?? "Applied",
    specialization: toStr(card.specialization) ?? "",
    appliedOn: toStr(card.applied_at) ?? "",
    acceptedOn: toStr(card.accepted_at),
    rejectedOn: toStr(card.rejected_at),
    withdrawnOn: toStr(card.withdrawn_at),
    startsAt: toStr(card.starts_at),
    endsAt: toStr(card.ends_at),
    duration: toNum(card.duration),
    isPaid: toBool(card.is_paid),
    canWithdraw: toBool(card.can_withdraw),
    rejectionReasonCode: toStr(card.rejection_reason),
    rejectionNote: toStr(card.rejection_note) ?? null,
    motivationalMessage: toStr(card.motivational_message),
    paymentStatus: toStr(card.payment_status) as MyApplication["paymentStatus"],
    bankAccount: normalizeBankAccount(card.bank_account),
    trial: normalizeTrial(card.free_trial_days, card.free_trial_days_remaining),
  };

  // may_lead_to_hire is a real boolean from the API (mirrors is_paid) and is
  // only sent on Applied/Accepted — toBool accepts both true and 1. Kept as an
  // "only set when true" so the card pill branches on presence consistently.
  if (toBool(card.may_lead_to_hire)) {
    item.mayLeadToHire = true;
  }

  return item;
}

/** Unwraps the { items, pagination } envelope and returns the mapped cards
 *  plus the server-reported total (shown in the page header only while the
 *  All view is active). Defensive against an empty payload, like the listings
 *  layer. */
export function normalizeApplicationsResponse(raw: unknown): {
  items: MyApplication[];
  total: number;
} {
  const body = (raw ?? {}) as UnknownRecord;
  const itemsRaw = Array.isArray(body.items) ? body.items : [];
  const pagination = (body.pagination ?? {}) as UnknownRecord;
  const total = Number(pagination.total ?? itemsRaw.length);

  return { items: itemsRaw.map(normalizeApplicationItem), total };
}