"use client"

import { useMutation, useQueryClient } from "@tanstack/react-query"

import { apiClient, ensureCsrfCookie } from "@/lib/api/client"
import { authUserQueryKey } from "@/lib/auth/auth-provider"
import type { AuthUser } from "@/lib/auth/types"

export type LoginPayload = {
  email: string
  password: string
}

export function useLogin() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: async (payload: LoginPayload) => {
      await ensureCsrfCookie()

      const { data } = await apiClient.post<{ data: AuthUser }>(
        "/auth/login",
        payload
      )

      return data.data
    },
    onSuccess: (user) => {
      queryClient.setQueryData(authUserQueryKey, user)
    },
  })
}
