interface SectionCardProps {
  id: string;
  heading: string;
  paragraphs: string[];
}

export default function SectionCard({
  id,
  heading,
  paragraphs,
}: SectionCardProps) {
  return (
    <section id={id}>
      {/* Header */}
      <h2 className="font-heading text-xl font-semibold tracking-tight text-primary-text">
        {heading}
      </h2>

      {/* Description */}
      <div className="mt-3 space-y-3">
        {paragraphs.map((paragraph, index) => (
          <p
            key={index}
            className="text-base leading-relaxed text-foreground/80"
          >
            {paragraph}
          </p>
        ))}
      </div>
    </section>
  );
}
