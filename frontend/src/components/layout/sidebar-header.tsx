"use client"

import Link from "next/link"
import { ChartCandlestick, PanelLeftClose, PanelLeftOpen } from "lucide-react"

import { cn } from "@/lib/utils"
import { Button } from "@/components/ui/button"
import {
  Tooltip,
  TooltipContent,
  TooltipTrigger,
} from "@/components/ui/tooltip"

export function SidebarHeader({
  collapsed,
  onToggleCollapsed,
}: {
  collapsed: boolean
  onToggleCollapsed: () => void
}) {
  return (
    <div
      className={cn(
        "flex h-16 shrink-0 items-center gap-2 px-3",
        collapsed ? "flex-col justify-center gap-1.5 py-2" : "justify-between"
      )}
    >
      <Link
        href="/dashboard"
        className="flex min-w-0 items-center gap-2 rounded-lg px-1 outline-none focus-visible:ring-2 focus-visible:ring-ring/50"
      >
        <span className="flex size-8 shrink-0 items-center justify-center rounded-lg bg-primary text-primary-foreground">
          <ChartCandlestick className="size-4.5" />
        </span>
        {!collapsed && (
          <span className="flex min-w-0 flex-col leading-none">
            <span className="truncate font-heading text-sm font-semibold text-foreground">
              TradeGrowth
            </span>
            <span className="truncate text-[11px] text-muted-foreground">
              Trading Performance OS
            </span>
          </span>
        )}
      </Link>

      <Tooltip>
        <TooltipTrigger
          render={
            <Button
              variant="ghost"
              size="icon-sm"
              onClick={onToggleCollapsed}
              aria-label={collapsed ? "Expand sidebar" : "Collapse sidebar"}
            />
          }
        >
          {collapsed ? (
            <PanelLeftOpen className="size-4" />
          ) : (
            <PanelLeftClose className="size-4" />
          )}
        </TooltipTrigger>
        <TooltipContent side={collapsed ? "right" : "bottom"}>
          {collapsed ? "Expand sidebar" : "Collapse sidebar"}
        </TooltipContent>
      </Tooltip>
    </div>
  )
}
