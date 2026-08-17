"use client";

import { createContext, useContext, useMemo, useState } from "react";
import type { ReactNode } from "react";

import type { SignupDraft } from "../../types";

type SignupDraftContextValue = {
  draft: SignupDraft | null;
  saveDraft: (draft: SignupDraft) => void;
};

const SignupDraftContext = createContext<SignupDraftContextValue | null>(null);

export function SignupDraftProvider({ children }: { children: ReactNode }) {
  const [draft, setDraft] = useState<SignupDraft | null>(null);

  const value = useMemo<SignupDraftContextValue>(
    () => ({
      draft,
      saveDraft: setDraft,
    }),
    [draft],
  );

  return (
    <SignupDraftContext.Provider value={value}>
      {children}
    </SignupDraftContext.Provider>
  );
}

export function useSignupDraft() {
  const context = useContext(SignupDraftContext);
  if (!context) {
    throw new Error("useSignupDraft must be used within a SignupDraftProvider.");
  }
  return context;
}