import { create } from "zustand"
import { persist } from "zustand/middleware"

// Which trading account the whole app is currently scoped to
// (FRONTEND_ARCHITECTURE.md §2.4) — lives in Zustand + localStorage, not URL
// state, so switching accounts feels instant across every page.
type AccountStore = {
  selectedAccountId: string | null
  setSelectedAccountId: (id: string | null) => void
}

export const useAccountStore = create<AccountStore>()(
  persist(
    (set) => ({
      selectedAccountId: null,
      setSelectedAccountId: (id) => set({ selectedAccountId: id }),
    }),
    { name: "tradegrowth:selected-account" }
  )
)
