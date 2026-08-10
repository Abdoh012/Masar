import type { PageIntroData } from "../../types";
import Header from "./Header";
import Title from "./Title";

export function PageIntro({ eyebrow, title, summary }: PageIntroData) {
  return (
    <header className="flex flex-col items-center gap-4 pb-10 text-center sm:pb-14">
      <Header title={eyebrow} />

      <Title title={title} />

      {/* Summary */}
      {summary ? (
        <p className="max-w-2xl text-base leading-relaxed text-muted-foreground sm:text-lg">
          {summary}
        </p>
      ) : null}
    </header>
  );
}
