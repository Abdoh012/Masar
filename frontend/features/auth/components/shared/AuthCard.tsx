import type { ReactNode } from "react";

import Motion from "@/shared/components/animation/Motion";
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from "@/shared/components/ui/card";
import { fadeInUp } from "@/shared/lib/animations";

interface AuthCardProps {
  title: string;
  description?: ReactNode;
  footer?: ReactNode;
  children: ReactNode;
}

export function AuthCard({
  title,
  description,
  footer,
  children,
}: AuthCardProps) {
  return (
    <Motion
      variants={fadeInUp}
      initial="hidden"
      animate="visible"
      className="w-full max-w-md"
    >
      <Card className="rounded-2xl border-border bg-card shadow-card">
        {/* Header */}
        {/* Header */}
        <CardHeader className="gap-2 p-8 pb-0 text-center sm:p-10 sm:pb-0">
          <p className="text-2xl font-semibold text-primary-text">{title}</p>
          <p>{description}</p>
        </CardHeader>

        {/* Main Content */}
        <CardContent className="p-8 pt-6 sm:p-10 sm:pt-6">
          {children}
        </CardContent>

        {/* Footer if exists */}
        {footer ? (
          <CardFooter className="flex-col gap-1 border-t border-border p-8 pt-5 text-center sm:p-10 sm:pt-5">
            {footer}
          </CardFooter>
        ) : null}
      </Card>
    </Motion>
  );
}
