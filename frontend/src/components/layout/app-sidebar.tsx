"use client"

import { cn } from "@/lib/utils"
import { useSidebarStore } from "@/lib/store/sidebar-store"
import { SidebarHeader } from "@/components/layout/sidebar-header"
import { SidebarAccountSelector } from "@/components/layout/sidebar-account-selector"
import { SidebarAddTradeButton } from "@/components/layout/sidebar-add-trade-button"
import { SidebarNav } from "@/components/layout/sidebar-nav"
import { SidebarFooter } from "@/components/layout/sidebar-footer"

export const SIDEBAR_WIDTH_EXPANDED = 272
export const SIDEBAR_WIDTH_COLLAPSED = 72

export function AppSidebar() {
  const { collapsed, toggle } = useSidebarStore()

  return (
    <aside
      className={cn(
        "fixed inset-y-0 left-0 z-30 hidden flex-col border-r border-border bg-sidebar text-sidebar-foreground transition-[width] duration-150 md:flex"
      )}
      style={{
        width: collapsed ? SIDEBAR_WIDTH_COLLAPSED : SIDEBAR_WIDTH_EXPANDED,
      }}
    >
      <SidebarHeader collapsed={collapsed} onToggleCollapsed={toggle} />
      <SidebarAccountSelector collapsed={collapsed} />
      <SidebarAddTradeButton collapsed={collapsed} />
      <SidebarNav collapsed={collapsed} />
      <SidebarFooter collapsed={collapsed} />
    </aside>
  )
}
