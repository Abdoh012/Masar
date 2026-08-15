"use client";

import { cn } from "@/shared/lib/utils";

interface EmptyCertificatesStateProps {
  variant: "both-empty" | "eligible-only" | "earned-only";
  onRelevantCTA?: () => void;
}

export function EmptyCertificatesState({
  variant,
  onRelevantCTA,
}: EmptyCertificatesStateProps) {
  switch (variant) {
    case "both-empty":
      return (
        <p className="text-sm text-muted-foreground">
          No certificates yet — complete a training to earn your first certificate.
        </p>
      );

    case "eligible-only":
      return (
        <p className="text-sm text-muted-foreground">
          No eligible trainings yet — complete a training to receive a certificate.
        </p>
      );

    case "earned-only":
      return (
        <p className="text-sm text-muted-foreground">
          Your completed trainings will appear here once you request a certificate.
        </p>
      );
  }
}