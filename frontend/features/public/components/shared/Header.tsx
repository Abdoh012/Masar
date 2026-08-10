export default function Header({ title }: { title: string }) {
  return (
    <h1 className="font-heading font-bold text-3xl capitalize text-secondary-text">
      {title}
    </h1>
  );
}
