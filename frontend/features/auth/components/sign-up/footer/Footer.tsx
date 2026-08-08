import Link from "next/link";

import { Checkbox } from "@/shared/components/ui/checkbox";
import { Label } from "@/shared/components/ui/label";
import CustomLink from "./CustomLink";

export default function Footer() {
  return (
    <div className="space-y-1.5">
      <div className="flex items-start gap-2.5">
        <Checkbox id="terms" name="terms" className="mt-0.5 cursor-pointer" />

        {/* label */}
        <Label
          htmlFor="terms"
          className="items-start text-sm font-normal leading-snug text-muted-foreground"
        >
          I agree to the <CustomLink title="Terms of Service" href="/terms" />
          and <CustomLink title="Privacy Policy" href="/privacy" />.
        </Label>
      </div>
    </div>
  );
}
