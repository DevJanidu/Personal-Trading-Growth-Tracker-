"use client"

import { createContext, useContext, useEffect } from "react"
import { useQuery } from "@tanstack/react-query"
import axios from "axios"

import { apiClient, ensureCsrfCookie } from "@/lib/api/client"
import type { AuthUser } from "@/lib/auth/types"

export const authUserQueryKey = ["auth", "user"] as const

async function fetchCurrentUser(): Promise<AuthUser | null> {
  try {
    const { data } = await apiClient.get<{ data: AuthUser }>("/auth/user")

    return data.data
  } catch (error) {
    if (axios.isAxiosError(error) && error.response?.status === 401) {
      return null
    }

    throw error
  }
}

type AuthContextValue = {
  user: AuthUser | null
  isLoading: boolean
  isAuthenticated: boolean
}

const AuthContext = createContext<AuthContextValue | null>(null)

export function AuthProvider({ children }: { children: React.ReactNode }) {
  useEffect(() => {
    void ensureCsrfCookie()
  }, [])

  const { data: user, isLoading } = useQuery({
    queryKey: authUserQueryKey,
    queryFn: fetchCurrentUser,
    retry: false,
    staleTime: 5 * 60 * 1000,
  })

  const value: AuthContextValue = {
    user: user ?? null,
    isLoading,
    isAuthenticated: Boolean(user),
  }

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>
}

export function useAuth(): AuthContextValue {
  const context = useContext(AuthContext)

  if (!context) {
    throw new Error("useAuth must be used within an AuthProvider")
  }

  return context
}
