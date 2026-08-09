import { Mail, MessageCircle } from "lucide-react";

import { Card } from "@/shared/components/ui/card";
import { SUPPORT_CONTACT } from "./support-content.content";

// SupportContactSection: email + channel contact info (no form markup —
// the ContactForm lives in its own leaf). US4/FR-015.
export function SupportContactSection() {
  return (
    <div className="grid w-full gap-4 sm:grid-cols-2">
      <Card className="flex items-start gap-4 p-6">
        <Mail className="mt-0.5 size-5 shrink-0 text-secondary-text" aria-hidden="true" strokeWidth={2} />
        <div className="flex flex-col gap-1">
          <p className="font-medium text-primary-text">{SUPPORT_CONTACT.channelLabel}</p>
          <a
            href={`mailto:${SUPPORT_CONTACT.email}`}
            className="text-sm text-muted-foreground underline-offset-4 transition-[color,background-color,box-shadow] duration-200 ring-2 ring-transparent hover:text-primary-text focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
          >
            {SUPPORT_CONTACT.channelValue}
          </a>
        </div>
      </Card>
      <Card className="flex items-start gap-4 p-6">
        <MessageCircle className="mt-0.5 size-5 shrink-0 text-secondary-text" aria-hidden="true" strokeWidth={2} />
        <div className="flex flex-col gap-1">
          <p className="font-medium text-primary-text">Response time</p>
          <p className="text-sm text-muted-foreground">{SUPPORT_CONTACT.responseTime}</p>
        </div>
      </Card>
    </div>
  );
}