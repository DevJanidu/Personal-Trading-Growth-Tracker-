"use client"

import Link from "next/link"
import { ChartCandlestick } from "lucide-react"

import { MobileSidebar } from "@/components/layout/mobile-sidebar"

// SRS §140 — compact sticky top header shown below the desktop breakpoint.
// Does not duplicate the full desktop sidebar header.
export function MobileHeader() {
  return (
    <header className="sticky top-0 z-20 flex h-14 items-center justify-between gap-2 border-b border-border bg-background px-3 md:hidden">
      <div className="flex items-center gap-2">
        <MobileSidebar />
        <Link href="/dashboard" className="flex items-center gap-2">
          <span className="flex size-7 shrink-0 items-center justify-center rounded-lg bg-primary text-primary-foreground">
            <ChartCandlestick className="size-4" />
          </span>
          <span className="font-heading text-sm font-semibold text-foreground">
            TradeGrowth
          </span>
        </Link>
      </div>
    </header>
  )
}
