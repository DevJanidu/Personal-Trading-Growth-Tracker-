"use client"

import { Plus } from "lucide-react"

import { cn } from "@/lib/utils"
import { Button } from "@/components/ui/button"
import {
  Tooltip,
  TooltipContent,
  TooltipTrigger,
} from "@/components/ui/tooltip"

/**
 * SRS §129 — highly visible primary action. Trade journaling ships in
 * IMPLEMENTATION_PLAN.md Phase 1d, not this phase, so the button is present
 * (per the sidebar spec) but disabled with an explanatory tooltip rather than
 * linking to a route that doesn't exist yet.
 */
export function SidebarAddTradeButton({ collapsed }: { collapsed: boolean }) {
  const button = (
    <Button
      disabled
      className={cn("w-full", collapsed && "w-8 px-0")}
      size={collapsed ? "icon" : "default"}
    >
      <Plus className="size-4" />
      {!collapsed && "Add Trade"}
    </Button>
  )

  return (
    <div className="px-2 py-2">
      <Tooltip>
        <TooltipTrigger render={<span className="block" />}>
          {button}
        </TooltipTrigger>
        <TooltipContent side={collapsed ? "right" : "bottom"}>
          Trade journaling is coming in the next phase
        </TooltipContent>
      </Tooltip>
    </div>
  )
}
