"use client"

import { ThemeProvider as NextThemesProvider } from "next-themes"

// SRS §45.2 — dark, light, and system theme support.
export function ThemeProvider({ children }: { children: React.ReactNode }) {
  return (
    <NextThemesProvider attribute="class" defaultTheme="system" enableSystem>
      {children}
    </NextThemesProvider>
  )
}
