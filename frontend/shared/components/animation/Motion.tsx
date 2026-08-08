"use client";

import { motion, type Variants } from "framer-motion";
import { useMemo, type ElementType, type ReactNode } from "react";

interface MotionProps {
  as?: keyof typeof motion;
  variants?: Variants;
  children?: ReactNode;
  [key: string]: unknown;
}

// Motion: generic animated wrapper — pick the underlying element via `as`,
// pass any of the shared variants (fadeInUp, scaleIn, etc.) from
// shared/lib/animations.ts.
//
// Uses motion.create() instead of indexing motion[as] directly: bracket
// indexing (motion[as] as ElementType) breaks in Framer Motion v11+.
export default function Motion({ as = "div", variants, children, ...props }: MotionProps) {
  const Component = useMemo(() => motion.create(as as ElementType), [as]);

  return (
    <Component variants={variants} {...props}>
      {children}
    </Component>
  );
}
