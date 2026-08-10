import { Mail, MessageCircle } from "lucide-react";

import { Card } from "@/shared/components/ui/card";
import { SUPPORT_CONTACT } from "./support-content.content";

export function SupportContactSection() {
  return (
    <div className="grid w-full gap-4 sm:grid-cols-2">
      <Card className="flex items-start gap-4 p-6">
        {/* Icon */}
        <Mail
          className="mt-0.5 size-5 shrink-0 text-secondary-text"
          aria-hidden="true"
          strokeWidth={2}
        />

        {/* Email + label */}
        <div className="flex flex-col gap-1">
          {/* Channel label */}
          <p className="font-medium text-primary-text">
            {SUPPORT_CONTACT.channelLabel}
          </p>

          {/* Email - make email clickable */}
          <a
            href={`mailto:${SUPPORT_CONTACT.email}`}
            className="text-sm text-muted-foreground  transition-all duration-200  hover:text-primary-text underline"
          >
            {SUPPORT_CONTACT.channelValue}
          </a>

          <p className="text-sm text-muted-foreground">
            {SUPPORT_CONTACT.responseTime}
          </p>
        </div>
      </Card>
    </div>
  );
}
