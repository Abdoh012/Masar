import { Input } from "@/shared/components/ui/input";
import { Label } from "@/shared/components/ui/label";

interface ApplicationFieldProps {
  id: string;
  name: string;
  label: string;
  placeholder?: string;
  type?: string;
  value: string;
  onChange: (value: string) => void;
  required?: boolean;
  optional?: boolean;
  autoComplete?: string;
}

// ApplicationField: the Label + Input/Textarea leaf shared by the apply wizard's
// steps. Controlled — the wizard keeps every value in the container so nothing
// is lost when navigating between steps (structure rules §10's controlled
// exception for wizards with conditional fields + chip selects). Required and
// optional states are pure props; validation is the native `required` attribute.
export function ApplicationField({
  id,
  name,
  label,
  placeholder,
  type = "text",
  value,
  onChange,
  required,
  optional,
  autoComplete,
}: ApplicationFieldProps) {
  return (
    <div className="space-y-1.5">
      <div className="flex items-center justify-between gap-2">
        <Label htmlFor={id}>{label}</Label>
        {optional ? (
          <span className="text-xs text-muted-foreground">(optional)</span>
        ) : null}
      </div>

      {type === "textarea" ? (
        <textarea
          id={id}
          name={name}
          placeholder={placeholder}
          required={required}
          value={value}
          onChange={(event) => onChange(event.target.value)}
          rows={4}
          className="w-full rounded-md border border-input bg-card px-3 py-2 text-sm text-foreground transition-[border-color,box-shadow] duration-200 ring-2 ring-transparent focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring placeholder:text-muted-foreground"
        />
      ) : (
        <Input
          id={id}
          name={name}
          type={type}
          placeholder={placeholder}
          required={required}
          autoComplete={autoComplete}
          value={value}
          onChange={(event) => onChange(event.target.value)}
        />
      )}
    </div>
  );
}