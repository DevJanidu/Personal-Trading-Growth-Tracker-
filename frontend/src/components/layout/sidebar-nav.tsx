import { navigation } from "@/config/navigation"
import { SidebarNavGroup } from "@/components/layout/sidebar-nav-group"

export function SidebarNav({
  collapsed,
  onNavigate,
}: {
  collapsed: boolean
  onNavigate?: () => void
}) {
  return (
    <nav
      aria-label="Main navigation"
      className="flex-1 space-y-1 overflow-y-auto px-2 pb-4"
    >
      {navigation.map((group, index) => (
        <SidebarNavGroup
          key={group.label ?? `group-${index}`}
          group={group}
          collapsed={collapsed}
          onNavigate={onNavigate}
        />
      ))}
    </nav>
  )
}
