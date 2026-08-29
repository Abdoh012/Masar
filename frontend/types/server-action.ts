// Shape returned by server actions used with useActionState.
export interface ActionState<T = unknown> {
  success?: boolean;
  message?: string;
  error?: string;
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
  errors?: Record<string, string[]>;
  userData?: object;
  message?: string;
  cookies?: Array<{ name: string; value: string }>;
};

export type TryCatchRequest = {
  url: string;
  method?: string;
  body?: object | FormData;
  cache?: RequestCache;
  revalidate?: number;
};
