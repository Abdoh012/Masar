import { CheckCircle2 } from "lucide-react";

import { fetchPaymentStatus } from "../../api";
import { PAYMENT_REPORT } from "./constants";
import { ReportPaymentForm } from "./ReportPaymentForm";

interface ReportPaymentZoneProps {
  applicationId: number;
}

// ReportPaymentZone: server component for the accepted-paid card's payment
// report area. Reads GET /applications/{id}/payment during render — the card
// DTO's payment_status stays "pending" whether or not a reference was already
// reported, so `data.submitted` is the only source for the reported state:
//  - submitted → renders the "Payment reported" panel entirely server-side
//  - otherwise → renders the ReportPaymentForm client leaf (the submit +
//    its loading/error feedback can't be server-rendered).
// After a successful submit, reportPayment revalidates /applications and
// Next's automatic route refresh re-renders this zone — the read now returns
// submitted:true and the panel swaps in with no client success state.
// Read failures degrade to the form (idle), same as an unreported card.
export async function ReportPaymentZone({
  applicationId,
}: ReportPaymentZoneProps) {
  const payment = await fetchPaymentStatus(applicationId);
  const paymentData =
    payment.success === true
      ? (payment.data as { submitted?: boolean } | undefined)
      : undefined;
  const submitted = paymentData?.submitted === true;

  if (submitted) {
    return (
      <div className="flex items-start gap-2.5">
        <CheckCircle2 className="mt-0.5 size-4 shrink-0 text-success-fg" aria-hidden />
        <p className="text-sm leading-relaxed text-success-fg">
          {PAYMENT_REPORT.successMessage}
        </p>
      </div>
    );
  }

  return <ReportPaymentForm applicationId={applicationId} />;
}