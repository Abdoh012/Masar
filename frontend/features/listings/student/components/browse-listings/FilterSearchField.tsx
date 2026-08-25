"use client";

import { useEffect, useState } from "react";

import { Search } from "lucide-react";

import { Button } from "@/shared/components/ui/button";
import { Input } from "@/shared/components/ui/input";

interface FilterSearchFieldProps {
  placeholder: string;
  value: string;
  onSearchChange: (query: string) => void;
}

export function FilterSearchField({
  placeholder,
  value,
  onSearchChange,
}: FilterSearchFieldProps) {
  const [local, setLocal] = useState(value);

  useEffect(() => {
    setLocal(value);
  }, [value]);

  function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    onSearchChange(local);
  }

  return (
    <form onSubmit={handleSubmit} className="relative w-full sm:w-1/3 mb-4 sm:mb-0">
      <Search className="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
      <Input
        type="search"
        placeholder={placeholder}
        value={local}
        onChange={(e) => setLocal(e.target.value)}
        className="rounded-full py-5 pl-9 pr-24"
      />
      <Button
        type="submit"
        size="sm"
        className="absolute right-1 top-1/2 -translate-y-1/2 rounded-full cursor-pointer"
      >
        Search
      </Button>
    </form>
  );
}
