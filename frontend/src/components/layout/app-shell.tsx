"use client"

import {
  AppSidebar,
  SIDEBAR_WIDTH_COLLAPSED,
  SIDEBAR_WIDTH_EXPANDED,
} from "@/components/layout/app-sidebar"
import { MobileHeader } from "@/components/layout/mobile-header"
import { useSidebarStore } from "@/lib/store/sidebar-store"

export function AppShell({ children }: { children: React.ReactNode }) {
  const { collapsed } = useSidebarStore()

  return (
    <div className="min-h-screen">
      <AppSidebar />
      <div
        className="flex min-h-screen flex-col transition-[margin] duration-150 md:ml-(--sidebar-width)"
        style={
          {
            "--sidebar-width": `${
              collapsed ? SIDEBAR_WIDTH_COLLAPSED : SIDEBAR_WIDTH_EXPANDED
            }px`,
          } as React.CSSProperties
        }
      >
        <MobileHeader />
        <main className="flex flex-1 flex-col">{children}</main>
      </div>
    </div>
  )
}
