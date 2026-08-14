"use client";

import { useState } from "react";

import type { MyApplication } from "../../types";
import type { TabValue } from "./constants";
import {
  APPLICATIONS_TITLE,
  EMPTY_STATES,
  MOCK_APPLICATIONS,
  TABS,
  matchesTab,
} from "./constants";
import { ApplicationCard } from "./ApplicationCard";
import { ApplicationStatusTabs } from "./ApplicationStatusTabs";
import { EmptyApplicationsState } from "./EmptyApplicationsState";
import { WithdrawConfirmDialog } from "./WithdrawConfirmDialog";

// MyApplicationsPage: orchestrator for the /applications page (FR-001/002/007).
// "use client" because it owns the list state, the active-tab filter, and the
// withdraw dialog target. Seeds the list from the mock constants, derives the
// per-tab counts and the header total from that single state, and filters +
// sorts newest-applied-first client-side — no refetch on tab change, so there
// is no loading flicker (FR-006/008). Withdraw is a local status transition
// (Applied → Withdrawn, in place, no navigation — FR-023, Clarification Q1);
// tab filtering, counts, and the header total update automatically because
// they all derive from the one applications state.
export function MyApplicationsPage() {
  const [applications, setApplications] = useState<MyApplication[]>(MOCK_APPLICATIONS);
  const [activeTab, setActiveTab] = useState<TabValue>("all");
  const [withdrawTarget, setWithdrawTarget] = useState<MyApplication | null>(null);

  const handleWithdraw = (application: MyApplication) => setWithdrawTarget(application);

  const handleWithdrawConfirm = () => {
    if (!withdrawTarget) return;
    setApplications((current) =>
      current.map((application) =>
        application.id === withdrawTarget.id
          ? { ...application, status: "Withdrawn" }
          : application,
      ),
    );
    setWithdrawTarget(null);
  };

  const statusTabs = TABS.map((tab) => ({
    ...tab,
    count: applications.filter((application) => matchesTab(application.status, tab.value)).length,
  }));

  const filteredApplications = [...applications]
    .filter((application) => matchesTab(application.status, activeTab))
    .sort((a, b) => b.appliedOn.localeCompare(a.appliedOn));

  return (
    <div className="space-y-6">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <h1 className="font-sans text-2xl font-semibold text-primary-text">
          {APPLICATIONS_TITLE}
        </h1>
        <span className="rounded-full bg-primary-tint px-3 py-1 text-sm font-medium text-primary-text">
          {applications.length}
        </span>
      </div>

      <ApplicationStatusTabs tabs={statusTabs} active={activeTab} onSelect={setActiveTab} />

      {filteredApplications.length > 0 ? (
        <div className="space-y-4">
          {filteredApplications.map((application) => (
            <ApplicationCard
              key={application.id}
              application={application}
              onWithdraw={handleWithdraw}
            />
          ))}
        </div>
      ) : (
        <EmptyApplicationsState {...EMPTY_STATES[activeTab]} />
      )}

      <WithdrawConfirmDialog
        application={withdrawTarget}
        onOpenChange={(open) => {
          if (!open) setWithdrawTarget(null);
        }}
        onConfirm={handleWithdrawConfirm}
      />
    </div>
  );
}