"use client";

import type { ReactNode } from "react";
import { motion } from "framer-motion";

import Motion from "@/shared/components/animation/Motion";
import { containerVariants } from "@/shared/lib/animations";

interface StaggerGroupProps {
  children: ReactNode;
  as?: keyof typeof motion;
  className?: string;
}

// StaggerGroup: a container that reveals its children — the meaningful
// elements inside a section (headings, descriptions, buttons, list/grid
// items) — one by one via Framer Motion's native staggerChildren.
// Pass each child as <Motion variants={fadeInUp}>; the container drives
// hidden → visible when it enters the viewport and staggers the children.
// Use `as` + `className` when the container must carry a semantic element
// (e.g. "ol" for a list) or the section's layout classes.
export default function StaggerGroup({ children, as = "div", className }: StaggerGroupProps) {
  return (
    <Motion
      as={as}
      className={className}
      variants={containerVariants}
      initial="hidden"
      whileInView="visible"
      viewport={{ once: true, amount: 0.2 }}
    >
      {children}
    </Motion>
  );
}
