import { Input } from "@/shared/components/ui/input";
import { Label } from "@/shared/components/ui/label";

interface LabeledInputProps {
  id: string;
  name: string;
  label: string;
  placeholder?: string;
  required?: boolean;
  type?: string;
  min?: number | string;
  step?: string;
  inputMode?: "decimal" | "numeric";
  defaultValue?: string | number;
}

// LabeledInput: the shared Label + Input field used by the listing form's
// basic-info and pricing sections (FR-001/002). Client-bound presentational
// leaf — no directive required. Uncontrolled `name`d input read off FormData
// on submit; defaultValue prefills edit mode without controlling the field
// (structure rules §10 / R-7). Extracted so BasicInfoFields and PricingFields
// don't each repeat the Label/Input wrapper.
export function LabeledInput({
  id,
  name,
  label,
  placeholder,
  required,
  type = "text",
  min,
  step,
  inputMode,
  defaultValue,
}: LabeledInputProps) {
  return (
    <div className="space-y-1.5">
      <Label htmlFor={id}>{label}</Label>
      <Input
        id={id}
        name={name}
        type={type}
        min={min}
        step={step}
        inputMode={inputMode}
        placeholder={placeholder}
        required={required}
        defaultValue={defaultValue}
      />
    </div>
  );
}