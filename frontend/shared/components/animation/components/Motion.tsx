"use client";

import { motion, type Variants } from "framer-motion";
import type { ElementType, ReactNode } from "react";

interface MotionProps {
  as?: keyof typeof motion;
  variants?: Variants;
  children?: ReactNode;
  [key: string]: unknown;
}

export default function Motion({
  as = "div",
  variants,
  ...props
}: MotionProps) {
  const Component = motion[as] as ElementType;

  return (
    <Component {...props} variants={variants}>
      {props.children}
    </Component>
  );
}
