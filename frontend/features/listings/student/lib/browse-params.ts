// URL parameter mapping for the browse listings view. Derives the active search
// / sort / saved-only / pagination / filter state purely from the raw route
// searchParams — no fetching, no rendering (structure rules §6).

export interface BrowseParams {
  query: string;
  sort: string;
  savedOnly: boolean;
  page: number;
  trainingType?: string;
  mode?: string;
  paid?: string;
}

function getParam(value: string | string[] | undefined): string {
  return typeof value === "string" ? value : "";
}

export function parseBrowseParams(
  searchParams: Record<string, string | string[] | undefined>,
): BrowseParams {
  const trainingType = getParam(searchParams.training_type);
  const mode = getParam(searchParams.mode);
  const paid = getParam(searchParams.paid);

  return {
    query: getParam(searchParams.q),
    sort: getParam(searchParams.sort),
    savedOnly: searchParams.saved === "1",
    page: Math.max(1, Number(getParam(searchParams.page)) || 1),
    ...(trainingType ? { trainingType } : {}),
    ...(mode ? { mode } : {}),
    ...(paid ? { paid } : {}),
  };
}