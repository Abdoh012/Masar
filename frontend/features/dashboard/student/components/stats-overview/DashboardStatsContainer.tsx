import Motion from "@/shared/components/animation/Motion";
import { fadeInUp } from "@/shared/lib/animations";

import type { DashboardQuickStats } from "@/features/dashboard/student/types";

import { DASHBOARD_STATS } from "./constants";
import { DashboardStatCard } from "./DashboardStatCard";

// DashboardStatsContainer: the three headline counters from the API's
// `quick_stats`, in a three-up row that stacks on mobile. It owns the grid and
// the icon resolution; each card is a leaf. Presentation only — the counts
// arrive already fetched.
export function DashboardStatsContainer({ stats }: { stats: DashboardQuickStats }) {
  return (
    <Motion
      as="div"
      variants={fadeInUp}
      initial="hidden"
      whileInView="visible"
      viewport={{ once: true, margin: "-40px" }}
      className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3"
    >
      {DASHBOARD_STATS.map((stat) => {
        const Icon = stat.icon;
        return (
          <DashboardStatCard
            key={stat.key}
            label={stat.label}
            value={stats[stat.key]}
            description={stat.description}
            icon={<Icon aria-hidden="true" className="size-5" />}
            accentClass={stat.accentClass}
          />
        );
      })}
    </Motion>
  );
}
