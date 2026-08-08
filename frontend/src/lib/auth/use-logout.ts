"use client"

import { useMutation, useQueryClient } from "@tanstack/react-query"

import { apiClient } from "@/lib/api/client"
import { authUserQueryKey } from "@/lib/auth/auth-provider"

export function useLogout() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: async () => {
      await apiClient.post("/auth/logout")
    },
    onSuccess: () => {
      queryClient.setQueryData(authUserQueryKey, null)
      queryClient.clear()
    },
  })
}
