import { BellOff } from "lucide-react";

export default function NoNotifications() {
  return (
    <div className="flex flex-1 flex-col items-center justify-center gap-2 rounded-xl border border-dashed border-border bg-background px-4 py-8 text-center">
      <BellOff aria-hidden="true" className="size-6 text-muted-foreground" />
      <p className="text-sm font-semibold text-foreground">Nothing new</p>
      <p className="text-xs text-muted-foreground">
        We&apos;ll let you know when something happens.
      </p>
    </div>
  );
}
