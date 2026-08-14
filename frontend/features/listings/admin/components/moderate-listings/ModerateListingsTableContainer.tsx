"use client";

import { useMemo, useState } from "react";

import { EmptyState } from "../../../shared/components/empty-state/EmptyState";
import {
  FilterSelect,
} from "../../../shared/components/filter-controls/FilterSelect";
import { ResetFiltersButton } from "../../../shared/components/filter-controls/ResetFiltersButton";

import type { AdminListingRow } from "../../types";

import { ListingTableRow } from "./ListingTableRow";
import {
  ADMIN_FILTER_LISTS,
  MOCK_ADMIN_LISTINGS,
  MODERATION_LABELS,
} from "./constants";

// Admin moderation table orchestrator (FR-018/019/020). "use client" because
// it owns the disabled-flag UI state and the company/status filters. There is
// deliberately no approval action anywhere — only companies get approved, not
// individual listings (architecture §1). Platform-wide rows render through the
// ListingTableRow leaf.

export function ModerateListingsTableContainer() {
  const [disabledById, setDisabledById] = useState<Record<string, boolean>>(
    () =>
      Object.fromEntries(
        MOCK_ADMIN_LISTINGS.map((row) => [row.id, row.disabled]),
      ),
  );
  const [companyFilter, setCompanyFilter] = useState("");
  const [statusFilter, setStatusFilter] = useState("");

  const rows = useMemo<AdminListingRow[]>(() => {
    return MOCK_ADMIN_LISTINGS.filter((row) => {
      if (companyFilter && row.companyDisplay !== companyFilter) return false;
      if (statusFilter && row.status !== statusFilter) return false;
      return true;
    });
  }, [companyFilter, statusFilter]);

  function handleDisableToggle(listingId: string, disabled: boolean) {
    setDisabledById((prev) => ({ ...prev, [listingId]: disabled }));
  }

  const hasActiveFilters = companyFilter !== "" || statusFilter !== "";

  return (
    <div className="space-y-6">
      <div className="flex flex-wrap items-end justify-between gap-4">
        <div className="flex flex-wrap gap-4">
          <FilterSelect
            label={MODERATION_LABELS.companyFilter}
            value={companyFilter}
            onValueChange={setCompanyFilter}
            allLabel={MODERATION_LABELS.allCompanies}
            options={ADMIN_FILTER_LISTS.company}
            triggerClassName="w-48"
          />

          <FilterSelect
            label={MODERATION_LABELS.statusFilter}
            value={statusFilter}
            onValueChange={setStatusFilter}
            allLabel={MODERATION_LABELS.allStatuses}
            options={ADMIN_FILTER_LISTS.status}
            triggerClassName="w-40"
          />
        </div>

        {hasActiveFilters ? (
          <ResetFiltersButton
            onClick={() => {
              setCompanyFilter("");
              setStatusFilter("");
            }}
            label={MODERATION_LABELS.reset}
          />
        ) : null}
      </div>

      {rows.length > 0 ? (
        <div className="overflow-x-auto rounded-xl border border-border bg-card">
          <table className="w-full min-w-[640px] border-collapse text-left">
            <thead>
              <tr className="border-b border-border">
                <th className="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                  {MODERATION_LABELS.columnCompany}
                </th>
                <th className="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                  {MODERATION_LABELS.columnListing}
                </th>
                <th className="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                  {MODERATION_LABELS.columnMode}
                </th>
                <th className="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                  {MODERATION_LABELS.columnStatus}
                </th>
                <th className="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                  {MODERATION_LABELS.columnAction}
                </th>
              </tr>
            </thead>
            <tbody>
              {rows.map((row) => (
                <ListingTableRow
                  key={row.id}
                  listing={{ ...row, disabled: disabledById[row.id] ?? false }}
                  onDisableToggle={handleDisableToggle}
                />
              ))}
            </tbody>
          </table>
        </div>
      ) : (
        <EmptyState title={MODERATION_LABELS.emptyTitle} message={MODERATION_LABELS.emptyMessage} />
      )}
    </div>
  );
}
