import type { LucideIcon } from "lucide-react"
import {
  BadgeCheck,
  Brain,
  BookOpenCheck,
  CalendarClock,
  CalendarDays,
  CalendarRange,
  CandlestickChart,
  ChartNoAxesCombined,
  ChartSpline,
  ClipboardCheck,
  Clock3,
  FileChartColumn,
  Goal,
  Images,
  LayoutDashboard,
  Layers3,
  ListChecks,
  NotebookTabs,
  Settings,
  ShieldAlert,
  Target,
  TrendingDown,
  TrendingUp,
  TriangleAlert,
  WalletCards,
} from "lucide-react"

// Sidebar structure per SRS §125 / FRONTEND_ARCHITECTURE.md §2.2. Icon
// mapping per SRS §132. Configuration-driven so the sidebar never hardcodes
// nav markup per item (FRONTEND_ARCHITECTURE.md §146).
export type SidebarNavItem = {
  title: string
  href: string
  icon: LucideIcon
  /** Extra path prefixes that should also mark this item active (nested routes). */
  match?: string[]
}

export type SidebarNavGroup = {
  label?: string
  items: SidebarNavItem[]
}

export const navigation: SidebarNavGroup[] = [
  {
    items: [{ title: "Dashboard", href: "/dashboard", icon: LayoutDashboard }],
  },
  {
    label: "Trading",
    items: [
      { title: "Trade Journal", href: "/trades", icon: NotebookTabs },
      { title: "Trade Calendar", href: "/calendar", icon: CalendarDays },
      { title: "Screenshots", href: "/screenshots", icon: Images },
    ],
  },
  {
    label: "Analytics",
    items: [
      {
        title: "Overview",
        href: "/analytics",
        icon: ChartNoAxesCombined,
      },
      { title: "Strategies", href: "/analytics/strategies", icon: Target },
      { title: "Pairs", href: "/analytics/pairs", icon: CandlestickChart },
      { title: "Sessions", href: "/analytics/sessions", icon: Clock3 },
      { title: "Weekdays", href: "/analytics/weekdays", icon: CalendarRange },
      {
        title: "Setup Grades",
        href: "/analytics/setup-grades",
        icon: BadgeCheck,
      },
      { title: "Mistakes", href: "/analytics/mistakes", icon: TriangleAlert },
      { title: "Psychology", href: "/analytics/psychology", icon: Brain },
      { title: "Risk Analysis", href: "/analytics/risk", icon: ShieldAlert },
    ],
  },
  {
    label: "Performance",
    items: [
      {
        title: "Account Growth",
        href: "/performance/growth",
        icon: TrendingUp,
      },
      { title: "Equity Curve", href: "/performance/equity", icon: ChartSpline },
      { title: "Drawdown", href: "/performance/drawdown", icon: TrendingDown },
      {
        title: "Daily Performance",
        href: "/performance/daily",
        icon: CalendarClock,
      },
      {
        title: "Monthly Performance",
        href: "/performance/monthly",
        icon: CalendarRange,
      },
      { title: "Goals", href: "/performance/goals", icon: Goal },
    ],
  },
  {
    label: "Playbook",
    items: [
      {
        title: "Strategies",
        href: "/playbook/strategies",
        icon: BookOpenCheck,
      },
      { title: "Setups", href: "/playbook/setups", icon: Layers3 },
      { title: "Trading Rules", href: "/playbook/rules", icon: ListChecks },
      {
        title: "Checklists",
        href: "/playbook/checklists",
        icon: ClipboardCheck,
      },
    ],
  },
  {
    items: [
      { title: "Reports", href: "/reports", icon: FileChartColumn },
      { title: "Accounts", href: "/accounts", icon: WalletCards },
      { title: "Settings", href: "/settings", icon: Settings },
    ],
  },
]
