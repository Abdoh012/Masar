// SealMark: the Masar verified seal from masar-identity.html — two rings
// + checkmark. Fixed brand strokes (document artifact), sized via className.
export function SealMark({ className }: { className?: string }) {
  return (
    <svg viewBox="0 0 64 64" fill="none" aria-hidden="true" className={className}>
      <circle cx="32" cy="32" r="28" stroke="#1B2A4A" strokeWidth={2.5} />
      <circle cx="32" cy="32" r="21" stroke="#E8A33D" strokeWidth={1.5} />
      <path
        d="M22 33l7 7 13-15"
        stroke="#1B2A4A"
        strokeWidth={4}
        strokeLinecap="round"
        strokeLinejoin="round"
      />
    </svg>
  );
}