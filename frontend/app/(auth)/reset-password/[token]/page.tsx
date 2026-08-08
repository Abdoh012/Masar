import type { Metadata } from "next";

interface PageProps {
  params: Promise<{ token: string }>;
}

export async function generateMetadata({ params }: PageProps): Promise<Metadata> {
  const { token } = await params;
  return { title: `Reset Password — ${token}` };
}

export default async function Page({ params }: PageProps) {
  const { token } = await params;
  return (
    <div className="p-8">
      <h1 className="font-sans text-xl font-semibold text-navy">Reset Password</h1>
      <p className="mt-1 text-sm text-mid">{token}</p>
    </div>
  );
}
