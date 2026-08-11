// Shape returned by server actions used with useActionState.
export interface ActionState<T = undefined> {
  success: boolean;
  message?: string;
  data?: T;
  fieldErrors?: Record<string, string[]>;
}

export const initialActionState: ActionState = {
  success: false,
};

export type TryCatchResponse = {
  success?: boolean;
  data?: any;
  error?: string;
  userData?: object;
  message?: string;
};

export type TryCatchRequest = {
  url: string;
  method?: string;
  body?: object;
  cache?: RequestCache;
  revalidate?: number;
};
