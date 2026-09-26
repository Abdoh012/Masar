import { Variants } from "framer-motion";

export const fadeInUp: Variants = {
  hidden: { opacity: 0, y: 20 },
  visible: {
    opacity: 1,
    y: 0,
  },
};

export const fadeInDown: Variants = {
  hidden: { opacity: 0, y: -20 },
  visible: {
    opacity: 1,
    y: 0,
  },
};

export const fadeInLeft: Variants = {
  hidden: { opacity: 0, x: 20 },
  visible: {
    opacity: 1,
    x: 0,
  },
};

export const fadeInRight: Variants = {
  hidden: { opacity: 0, x: -20 },
  visible: {
    opacity: 1,
    x: 0,
  },
};

export const scaleIn: Variants = {
  hidden: { opacity: 0, scale: 0.95 },
  visible: {
    opacity: 1,
    scale: 1,
    transition: {
      duration: 0.25,
      ease: "easeOut",
    },
  },
};

export const rotateToggle: Variants = {
  closed: {
    rotate: 0,
    transition: { duration: 0.25, ease: "easeInOut" },
  },
  open: {
    rotate: 180,
    transition: { duration: 0.25, ease: "easeInOut" },
  },
};

// Pointer-driven reveal (e.g. a hover overlay on top of media). Deliberately
// hover-only: it is driven from the hovered element itself, so a focusable
// sibling outside it can never trigger it. Reach for a CSS focus-within group
// instead when the reveal must also answer keyboard focus.
export const hoverReveal: Variants = {
  hidden: {
    opacity: 0,
    transition: { duration: 0.24, ease: "easeOut" },
  },
  visible: {
    opacity: 1,
    transition: { duration: 0.24, ease: "easeOut" },
  },
};

export const expandCollapse: Variants = {
  hidden: {
    height: 0,
    opacity: 0,
    transition: { duration: 0.28, ease: "easeInOut" },
  },
  visible: {
    height: "auto",
    opacity: 1,
    transition: { duration: 0.28, ease: "easeInOut" },
  },
};

export const menuPanel: Variants = {
  hidden: {
    opacity: 0,
    y: -8,
    scale: 0.98,
    transition: { duration: 0.22, ease: "easeInOut" },
  },
  visible: {
    opacity: 1,
    y: 0,
    scale: 1,
    transition: { duration: 0.22, ease: "easeInOut" },
  },
};

export const containerVariants: Variants = {
  hidden: {},
  visible: {
    transition: {
      staggerChildren: 0.2,
    },
  },
};
