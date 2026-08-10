"use client";

import { motion, type Variants } from "framer-motion";
import { useMemo, type ElementType, type ReactNode } from "react";

interface MotionProps {
  as?: keyof typeof motion;
  variants?: Variants;
  children?: ReactNode;
  [key: string]: unknown;
}

export default function Motion({
  as = "div",
  variants,
  children,
  ...props
}: MotionProps) {
  const Component = useMemo(() => motion.create(as as ElementType), [as]);

  return (
    <Component {...props} variants={variants}>
      {children}
    </Component>
  );
}