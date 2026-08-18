import { Search } from "lucide-react";

import { Input } from "@/shared/components/ui/input";

interface FilterSearchFieldProps {
  placeholder: string;
}

export function FilterSearchField({ placeholder }: FilterSearchFieldProps) {
  return (
    <div className="relative w-full sm:w-1/3 mb-4 sm:mb-0">
      <Search className="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
      <Input
        type="search"
        placeholder={placeholder}
        className="rounded-full py-5 pl-9"
      />
    </div>
  );
}
