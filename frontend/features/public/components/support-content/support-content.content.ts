// Support page copy (US4) — intro, FAQ entries, contact channels.
import type { PageIntroData } from "../../types";
import type { FaqItem, SupportContactData } from "./types";

export const SUPPORT_INTRO: PageIntroData = {
  eyebrow: "Support",
  title: "How can we help?",
  summary:
    "Find quick answers to common questions, or reach the Masar team directly and we'll get back to you shortly.",
};

export const SUPPORT_FAQ: FaqItem[] = [
  {
    question: "How do I create an account?",
    answer:
      "Click Start now in the header, choose whether you are a student or a company, and follow the steps. You'll confirm your email before your account is active.",
  },
  {
    question: "Is Masar free for students?",
    answer:
      "Yes. Creating a profile, browsing trainings, and applying are free for students. Companies pay only if a placement is confirmed, never to browse candidates.",
  },
  {
    question: "How do companies verify my training?",
    answer:
      "When you complete a training, the company marks it complete and confirms the work you delivered. Masar then issues a verified certificate tied to your profile.",
  },
  {
    question: "Can I apply to more than one training?",
    answer:
      "Absolutely. You can apply to as many trainings as you like, and track every application from your dashboard so you never lose track of where you stand.",
  },
  {
    question: "What happens after I finish a training?",
    answer:
      "You earn a verified certificate and your completed work becomes part of your public profile, making it easier for other companies to find and hire you.",
  },
  {
    question: "Who do I contact if something goes wrong?",
    answer:
      "Reach us at support@masar.example with a short description of the issue and the email you signed up with. We typically reply within two business days.",
  },
];

export const SUPPORT_CONTACT: SupportContactData = {
  email: "support@masar.example",
  channelLabel: "Email",
  channelValue: "support@masar.example",
  responseTime: "We usually reply within two business days.",
};