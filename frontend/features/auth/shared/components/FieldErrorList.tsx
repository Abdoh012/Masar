interface FieldErrorListProps {
  errors?: string[];
}

// Renders every validation message for one field, so multiple backend errors
// (e.g. the register password rules) are all shown, not just the first.
export function FieldErrorList({ errors }: FieldErrorListProps) {
  if (!errors?.length) return null;

  return (
    <ul role="alert" className="space-y-1 text-sm text-error-fg">
      {errors.map((message) => (
        <li key={message}>{message}</li>
      ))}
    </ul>
  );
}