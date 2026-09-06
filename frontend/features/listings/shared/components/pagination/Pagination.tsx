"use client";

import { useRouter, useSearchParams } from "next/navigation";

import { ChevronLeft, ChevronRight } from "lucide-react";

import { Button } from "@/shared/components/ui/button";
import createPageUrl from "@/shared/lib/createPageUrl";

export interface PaginationInfo {
  current_page: number;
  total_pages: number;
}

interface PaginationProps {
  pagination: PaginationInfo;
}

// Pagination: shared pager for the browse grid. Reads the current URL params
// itself and pushes updates via router.push — the container only hands over the
// page numbers. Pure leaf, no data fetching (structure rules §6).
export function Pagination({ pagination }: PaginationProps) {
  const router = useRouter();
  const searchParams = useSearchParams();
  const sp = searchParams.toString();
  const { current_page, total_pages } = pagination;

  if (total_pages <= 1) return null;

  function goToPage(page: number) {
    router.push(createPageUrl("page", page, sp));
  }

  const pages: (number | "...")[] = [];
  for (let i = 1; i <= total_pages; i++) {
    if (i === 1 || i === total_pages || Math.abs(i - current_page) <= 1) {
      pages.push(i);
    } else if (pages[pages.length - 1] !== "...") {
      pages.push("...");
    }
  }

  return (
    <nav className="flex items-center justify-center gap-1 pt-8" aria-label="Pagination">
      <Button
        variant="outline"
        size="icon"
        disabled={current_page <= 1}
        onClick={() => goToPage(current_page - 1)}
        className="size-9 cursor-pointer"
      >
        <ChevronLeft className="size-4" />
      </Button>

      {pages.map((p, i) =>
        p === "..." ? (
          <span key={`ellipsis-${i}`} className="px-2 text-sm text-muted-foreground">
            ...
          </span>
        ) : (
          <Button
            key={p}
            variant={p === current_page ? "default" : "outline"}
            size="icon"
            onClick={() => goToPage(p)}
            className="size-9 cursor-pointer"
          >
            {p}
          </Button>
        ),
      )}

      <Button
        variant="outline"
        size="icon"
        disabled={current_page >= total_pages}
        onClick={() => goToPage(current_page + 1)}
        className="size-9 cursor-pointer"
      >
        <ChevronRight className="size-4" />
      </Button>
    </nav>
  );
}