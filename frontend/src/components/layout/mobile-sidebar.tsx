"use client"

import { useState } from "react"
import Link from "next/link"
import { ChartCandlestick, Menu } from "lucide-react"

import { Button } from "@/components/ui/button"
import {
  Sheet,
  SheetContent,
  SheetHeader,
  SheetTitle,
  SheetTrigger,
} from "@/components/ui/sheet"
import { SidebarAccountSelector } from "@/components/layout/sidebar-account-selector"
import { SidebarAddTradeButton } from "@/components/layout/sidebar-add-trade-button"
import { SidebarNav } from "@/components/layout/sidebar-nav"
import { SidebarFooter } from "@/components/layout/sidebar-footer"

// SRS §139 — slide-over drawer for < 768px, closes automatically on
// navigation.
export function MobileSidebar() {
  const [open, setOpen] = useState(false)

  return (
    <Sheet open={open} onOpenChange={setOpen}>
      <SheetTrigger
        render={
          <Button
            variant="ghost"
            size="icon-sm"
            aria-label="Open navigation menu"
          />
        }
      >
        <Menu className="size-5" />
      </SheetTrigger>
      <SheetContent
        side="left"
        className="flex w-[88vw] max-w-[320px] flex-col gap-0 p-0"
      >
        <SheetHeader className="h-16 shrink-0 flex-row items-center gap-2 border-b border-border px-4 py-0">
          <SheetTitle
            render={
              <Link
                href="/dashboard"
                onClick={() => setOpen(false)}
                className="flex items-center gap-2"
              />
            }
          >
            <span className="flex size-8 shrink-0 items-center justify-center rounded-lg bg-primary text-primary-foreground">
              <ChartCandlestick className="size-4.5" />
            </span>
            <span className="font-heading text-sm font-semibold text-foreground">
              TradeGrowth
            </span>
          </SheetTitle>
        </SheetHeader>
        <SidebarAccountSelector collapsed={false} />
        <SidebarAddTradeButton collapsed={false} />
        <SidebarNav collapsed={false} onNavigate={() => setOpen(false)} />
        <SidebarFooter collapsed={false} />
      </SheetContent>
    </Sheet>
  )
}
