import { cn } from "@/lib/utils"
import { SidebarNavItem } from "@/components/layout/sidebar-nav-item"
import type { SidebarNavGroup as SidebarNavGroupType } from "@/config/navigation"

export function SidebarNavGroup({
  group,
  collapsed,
  onNavigate,
}: {
  group: SidebarNavGroupType
  collapsed: boolean
  onNavigate?: () => void
}) {
  return (
    <div className="flex flex-col gap-0.5">
      {group.label && !collapsed && (
        <span className="px-3 pt-4 pb-1 text-xs font-medium tracking-wide text-muted-foreground uppercase">
          {group.label}
        </span>
      )}
      {group.label && collapsed && <div className={cn("pt-3")} />}
      {group.items.map((item) => (
        <SidebarNavItem
          key={item.href}
          item={item}
          collapsed={collapsed}
          onNavigate={onNavigate}
        />
      ))}
    </div>
  )
}
