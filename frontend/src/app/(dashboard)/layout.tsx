"use client"

import { useEffect } from "react"
import { useRouter } from "next/navigation"
import { Loader2 } from "lucide-react"

import { AppShell } from "@/components/layout/app-shell"
import { useAuth } from "@/lib/auth/auth-provider"

// Cookie-session SPA auth guard (FRONTEND_ARCHITECTURE.md §6): the session
// cookie is httpOnly, so there's nothing for server-side middleware/proxy to
// read without a network round trip — the client-side check after the
// /auth/user fetch is the simplest correct approach here.
export default function DashboardLayout({
  children,
}: {
  children: React.ReactNode
}) {
  const { isAuthenticated, isLoading } = useAuth()
  const router = useRouter()

  useEffect(() => {
    if (!isLoading && !isAuthenticated) {
      router.replace("/login")
    }
  }, [isLoading, isAuthenticated, router])

  if (isLoading || !isAuthenticated) {
    return (
      <div className="flex min-h-screen items-center justify-center">
        <Loader2 className="size-6 animate-spin text-muted-foreground" />
      </div>
    )
  }

  return <AppShell>{children}</AppShell>
}
