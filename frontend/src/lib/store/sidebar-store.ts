import { create } from "zustand"
import { persist } from "zustand/middleware"

// Desktop sidebar collapse state, persisted across reloads
// (FRONTEND_ARCHITECTURE.md §2.3).
type SidebarStore = {
  collapsed: boolean
  setCollapsed: (collapsed: boolean) => void
  toggle: () => void
}

export const useSidebarStore = create<SidebarStore>()(
  persist(
    (set) => ({
      collapsed: false,
      setCollapsed: (collapsed) => set({ collapsed }),
      toggle: () => set((state) => ({ collapsed: !state.collapsed })),
    }),
    { name: "tradegrowth:sidebar-collapsed" }
  )
)
