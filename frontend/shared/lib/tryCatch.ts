// Established pattern from your other projects: wrap a promise, get back
// a [data, error] tuple instead of throwing. Use this in every feature's
// api/ or actions/ layer instead of raw try/catch blocks.
export async function tryCatch<T>(
  promise: Promise<T>,
): Promise<[T, null] | [null, Error]> {
  try {
    const data = await promise;
    return [data, null];
  } catch (err) {
    return [null, err instanceof Error ? err : new Error(String(err))];
  }
}
