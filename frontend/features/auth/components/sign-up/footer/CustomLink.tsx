import Link from "next/link";

interface CustomLinkProps {
  href: string;
  title: string;
}

export default function CustomLink({ href, title }: CustomLinkProps) {
  return (
    <Link href={href} className="text-secondary-text hover:underline">
      {title}
    </Link>
  );
}
