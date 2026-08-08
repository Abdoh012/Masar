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
