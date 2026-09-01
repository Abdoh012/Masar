interface FieldErrorListProps {
  errors?: string | string[];
}

// Renders every validation message for one field, so multiple backend errors
// (e.g. the register password rules) are all shown, not just the first.
// Accepts a single message string or a list — the backend reports field errors
// as either shape — and normalizes it for rendering.
export function FieldErrorList({ errors }: FieldErrorListProps) {
  const list = Array.isArray(errors) ? errors : errors ? [errors] : [];
  if (list.length === 0) return null;

  return (
    <ul role="alert" className="space-y-1 text-sm text-error-fg">
      {list.map((message) => (
        <li key={message}>{message}</li>
      ))}
    </ul>
  );
}