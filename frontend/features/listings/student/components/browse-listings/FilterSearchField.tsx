import { Search } from "lucide-react";

import { Input } from "@/shared/components/ui/input";

interface FilterSearchFieldProps {
  placeholder: string;
}

// FilterSearchField: the keyword search box in the browse top bar. Static
// (uncontrolled) placeholder for the search — wired to real filtering later.
export function FilterSearchField({ placeholder }: FilterSearchFieldProps) {
  return (
    <div className="relative w-1/3">
      <Search
        aria-hidden="true"
        className="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground"
      />
      <Input
        type="search"
        placeholder={placeholder}
        className="rounded-full py-5 pl-9"
        aria-label={placeholder}
      />
    </div>
  );
}