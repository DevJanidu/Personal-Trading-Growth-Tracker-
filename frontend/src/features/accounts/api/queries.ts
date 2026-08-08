"use client"

import { useQuery } from "@tanstack/react-query"

import { apiClient } from "@/lib/api/client"
import { useAuth } from "@/lib/auth/auth-provider"
import { accountKeys } from "@/features/accounts/api/keys"
import type { TradingAccount } from "@/features/accounts/types/schema"

async function fetchAccounts(): Promise<TradingAccount[]> {
  const { data } = await apiClient.get<{ data: TradingAccount[] }>(
    "/accounts"
  )

  return data.data
}

export function useAccountsQuery() {
  const { isAuthenticated } = useAuth()

  return useQuery({
    queryKey: accountKeys.lists(),
    queryFn: fetchAccounts,
    enabled: isAuthenticated,
    staleTime: 60 * 1000,
  })
}
