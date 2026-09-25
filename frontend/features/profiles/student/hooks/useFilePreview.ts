"use client";

import { useCallback, useEffect, useRef, useState } from "react";

import type { SelectedFile } from "../types";

interface FilePreview {
  file: SelectedFile | null;
  select: (file: File) => void;
  clear: () => void;
}

// useFilePreview: holds a file the user picked in the browser and the object
// URL used to preview it, revoking the previous URL whenever the selection is
// replaced, cleared, or the owning component unmounts — an object URL pins the
// blob in memory until it's released. Shared by the identity avatar and the
// academic-status certificate dropzone (the same local-preview behaviour in two
// places). Nothing is uploaded: the file never leaves the page.
export function useFilePreview(): FilePreview {
  const [file, setFile] = useState<SelectedFile | null>(null);
  const previewUrlRef = useRef<string | null>(null);

  const release = useCallback(() => {
    if (previewUrlRef.current) {
      URL.revokeObjectURL(previewUrlRef.current);
      previewUrlRef.current = null;
    }
  }, []);

  useEffect(() => release, [release]);

  const select = useCallback(
    (next: File) => {
      release();

      const previewUrl = URL.createObjectURL(next);
      previewUrlRef.current = previewUrl;

      setFile({ name: next.name, size: next.size, type: next.type, previewUrl });
    },
    [release],
  );

  const clear = useCallback(() => {
    release();
    setFile(null);
  }, [release]);

  return { file, select, clear };
}
