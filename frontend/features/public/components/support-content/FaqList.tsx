"use client";

import { AnimatePresence, motion } from "framer-motion";
import { ChevronDown } from "lucide-react";
import { useState } from "react";

import type { FaqItem } from "../../types";

interface FaqListProps {
  items: FaqItem[];
}

// FaqList: animated accordion (FR-015/016). Smoothly expands/collapses the
// answer panel with a height animation; each trigger is a real <button>
// with aria-expanded/aria-controls so it stays keyboard-operable.
export function FaqList({ items }: FaqListProps) {
  const [openIndex, setOpenIndex] = useState<number | null>(null);

  const toggle = (index: number) =>
    setOpenIndex((current) => (current === index ? null : index));

  return (
    <div className="mx-auto w-full max-w-3xl space-y-3">
      {items.map((item, index) => {
        const isOpen = openIndex === index;
        const answerId = `faq-answer-${index}`;

        return (
          <div
            key={item.question}
            className="overflow-hidden rounded-xl border border-border bg-card shadow-card-sm"
          >
            {/* FAQ question trigger - accordion button */}
            <button
              type="button"
              onClick={() => toggle(index)}
              className="flex w-full min-h-11 cursor-pointer items-center justify-between gap-4 px-5 py-4 font-medium text-primary-text transition-[color,background-color,box-shadow] duration-200 ring-2 ring-transparent hover:bg-primary-tint focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
            >
              {/* FAQ question */}
              <span>{item.question}</span>

              {/* FAQ icon */}
              <motion.span
                animate={{ rotate: isOpen ? 180 : 0 }}
                transition={{ duration: 0.25, ease: "easeInOut" }}
                className="shrink-0 text-secondary-text"
              >
                <ChevronDown
                  className="size-4"
                  aria-hidden="true"
                  strokeWidth={2}
                />
              </motion.span>
            </button>

            {/* FAQ answer panel - animates when open/closed */}
            <AnimatePresence initial={false}>
              {isOpen ? (
                <motion.div
                  id={answerId}
                  key="answer"
                  initial={{ height: 0, opacity: 0 }}
                  animate={{ height: "auto", opacity: 1 }}
                  exit={{ height: 0, opacity: 0 }}
                  transition={{ duration: 0.28, ease: "easeInOut" }}
                  className="border-t border-border overflow-hidden"
                >
                  {/* FAQ answer text */}
                  <p className="px-5 py-4 text-sm leading-relaxed text-muted-foreground">
                    {item.answer}
                  </p>
                </motion.div>
              ) : null}
            </AnimatePresence>
          </div>
        );
      })}
    </div>
  );
}
