export default function Title({ title }: { title: string }) {
  return (
    <h2 className="font-heading mt-3 text-2xl font-semibold text-primary-text sm:text-3xl">
      {title}
    </h2>
  );
}
