import type { NextConfig } from "next";

const nextConfig: NextConfig = {
  experimental: {
    // Next caps a Server Action's request body at 1 MB by default. The profile
    // photo arrives through an action as multipart (backend cap 5 MB) and the
    // training-application wizard sends a CV (backend cap 10 MB), so the limit
    // has to cover the larger of the two.
    serverActions: {
      bodySizeLimit: "12mb",
    },
  },
};

export default nextConfig;
