import { PageNotFound } from "@/shared/components/error/PageNotFound";

export default function NotFound() {
  return <PageNotFound label="Reset link" backHref="/forgot-password" backLabel="Request a new link" />;
}
