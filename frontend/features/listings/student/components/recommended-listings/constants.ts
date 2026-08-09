import type { Listing } from "@/features/listings/shared/types";

// Field-matched mock listings for "Software Engineering" students.
export const RECOMMENDED_LISTINGS: Listing[] = [
  {
    id: "36",
    title: "Spring Boot Engineer Trainee",
    companyName: "Sawari Digital",
    field: "Software Engineering",
    location: "Cairo",
    mode: "TRAINEE",
    free: true,
  },
  {
    id: "41",
    title: "React Frontend Intern",
    companyName: "Mobica Alexandria",
    field: "Software Engineering",
    location: "Alexandria",
    mode: "INTERN",
  },
  {
    id: "52",
    title: "Quality & Test Engineer Program",
    companyName: "StartApp Hub",
    field: "Software Engineering",
    mode: "TRAINEE",
  },
  {
    id: "63",
    title: "DevOps Apprentice",
    companyName: "CloudiTech",
    field: "Software Engineering",
    mode: "APPRENTICE",
  },
];

// Fallback (general/newest) used when the student's field has no listings.
export const FALLBACK_LISTINGS: Listing[] = [
  {
    id: "70",
    title: "Digital Marketing Trainee",
    companyName: "BrightLocal Media",
    field: "Marketing",
    location: "Giza",
    mode: "TRAINEE",
  },
  {
    id: "71",
    title: "Data Analyst Intern",
    companyName: "Meridian Analytics",
    field: "Data Science",
    location: "Cairo",
    mode: "INTERN",
    free: true,
  },
  {
    id: "72",
    title: "UI/UX Design Apprentice",
    companyName: "Palette Studio",
    field: "Design",
    mode: "APPRENTICE",
  },
  {
    id: "73",
    title: "Frontend Developer Program",
    companyName: "Orbit Software",
    field: "Software Engineering",
    location: "Remote",
    mode: "TRAINEE",
    free: true,
  },
];