import Link from "next/link";

import Motion from "@/shared/components/animation/Motion";
import StaggerGroup from "@/shared/components/animation/StaggerGroup";
import {
  Card,
  CardContent,
  CardFooter,
  CardHeader,
} from "@/shared/components/ui/card";
import { Button } from "@/shared/components/ui/button";
import { fadeInUp } from "@/shared/lib/animations";

import { PendingNextSteps } from "./PendingNextSteps";
import { PendingStatusBadge } from "./PendingStatusBadge";
import { PendingStatusVisual } from "./PendingStatusVisual";
import { PENDING_APPROVAL } from "./constants";

// PendingApprovalContainer: composes the company "pending approval" gate
// card — status visual, heading, review badge, next steps, and a back-to-
// sign-in action. The StaggerGroup parent staggers each meaningful element
// (fadeInUp) via containerVariants; no manual delays. Pure UI, no wiring.
export default function PendingApprovalContainer() {
  return (
    <StaggerGroup className="w-full max-w-md">
      <Card className="rounded-2xl border-border bg-card shadow-card">
        <CardHeader className="items-center gap-4 p-8 pb-0 text-center sm:p-10 sm:pb-0">
          <Motion variants={fadeInUp}>
            <PendingStatusVisual />
          </Motion>

          <Motion variants={fadeInUp}>
            <h1 className="text-2xl font-semibold text-primary-text">
              {PENDING_APPROVAL.heading}
            </h1>
          </Motion>

          <Motion variants={fadeInUp}>
            <p className="text-sm leading-relaxed text-muted-foreground">
              {PENDING_APPROVAL.description}
            </p>
          </Motion>

          <Motion variants={fadeInUp}>
            <PendingStatusBadge />
          </Motion>
        </CardHeader>

        <CardContent className="p-8 pt-6 sm:p-10 sm:pt-6">
          <Motion variants={fadeInUp}>
            <PendingNextSteps />
          </Motion>
        </CardContent>

        <CardFooter className="flex-col gap-4 border-t border-border p-8 pt-5 sm:p-10 sm:pt-5">
          <Motion variants={fadeInUp} className="w-full">
            <Button asChild className="w-full">
              <Link href={PENDING_APPROVAL.action.href}>
                {PENDING_APPROVAL.action.label}
              </Link>
            </Button>
          </Motion>

          <Motion variants={fadeInUp}>
            <p className="text-sm text-muted-foreground">
              {PENDING_APPROVAL.footer.text}
              <Link
                href={PENDING_APPROVAL.footer.linkHref}
                className="font-medium text-secondary-text hover:underline"
              >
                {PENDING_APPROVAL.footer.linkLabel}
              </Link>
            </p>
          </Motion>
        </CardFooter>
      </Card>
    </StaggerGroup>
  );
}
