"use client"

import { useMutation, useQueryClient } from "@tanstack/react-query"

import { apiClient } from "@/lib/api/client"
import { accountKeys } from "@/features/accounts/api/keys"
import type {
  CreateTradingAccountInput,
  TradingAccount,
} from "@/features/accounts/types/schema"

async function createAccount(
  payload: CreateTradingAccountInput
): Promise<TradingAccount> {
  const { data } = await apiClient.post<{ data: TradingAccount }>(
    "/accounts",
    payload
  )

  return data.data
}

export function useCreateAccountMutation() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: createAccount,
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: accountKeys.all })
    },
  })
}
