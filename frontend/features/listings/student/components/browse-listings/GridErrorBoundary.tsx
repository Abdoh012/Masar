"use client";

import { Component, type ReactNode } from "react";

import { GridErrorState } from "./GridErrorState";

interface GridErrorBoundaryProps {
  children: ReactNode;
}

interface GridErrorBoundaryState {
  hasError: boolean;
}

// GridErrorBoundary: catches render errors from the suspendable browse grid so
// a failed fetch shows a retry panel over just the cards region — the hero and
// filter rows stay interactive instead of the whole route falling into the
// segment error page. Resetting re-renders the children (BrowseResults), which
// produces a fresh server render and re-attempts the fetch.
export class GridErrorBoundary extends Component<
  GridErrorBoundaryProps,
  GridErrorBoundaryState
> {
  state: GridErrorBoundaryState = { hasError: false };

  static getDerivedStateFromError(): GridErrorBoundaryState {
    return { hasError: true };
  }

  render(): ReactNode {
    if (this.state.hasError) {
      return <GridErrorState onRetry={() => this.setState({ hasError: false })} />;
    }
    return this.props.children;
  }
}