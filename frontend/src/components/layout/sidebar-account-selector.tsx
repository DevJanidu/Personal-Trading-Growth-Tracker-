"use client"

import { useEffect } from "react"
import Link from "next/link"
import { Check, ChevronsUpDown, Plus, Wallet } from "lucide-react"

import { cn } from "@/lib/utils"
import { Button } from "@/components/ui/button"
import { Badge } from "@/components/ui/badge"
import { Separator } from "@/components/ui/separator"
import { Skeleton } from "@/components/ui/skeleton"
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from "@/components/ui/popover"
import {
  Tooltip,
  TooltipContent,
  TooltipTrigger,
} from "@/components/ui/tooltip"
import { useAccountsQuery } from "@/features/accounts/api/queries"
import {
  accountTypeLabels,
  type TradingAccount,
} from "@/features/accounts/types/schema"
import { useAccountStore } from "@/lib/store/account-store"

function formatBalance(account: TradingAccount) {
  return new Intl.NumberFormat("en-US", {
    style: "currency",
    currency: account.currency || "USD",
    maximumFractionDigits: 0,
  }).format(Number(account.current_balance))
}

export function SidebarAccountSelector({ collapsed }: { collapsed: boolean }) {
  const { data: accounts, isLoading } = useAccountsQuery()
  const { selectedAccountId, setSelectedAccountId } = useAccountStore()

  const selectedAccount =
    accounts?.find((account) => account.id === selectedAccountId) ??
    accounts?.[0]

  useEffect(() => {
    if (!selectedAccountId && accounts && accounts.length > 0) {
      setSelectedAccountId(accounts[0].id)
    }
  }, [accounts, selectedAccountId, setSelectedAccountId])

  if (isLoading) {
    return (
      <div className="px-3 py-2">
        <Skeleton className={cn("h-11 rounded-lg", collapsed && "size-8")} />
      </div>
    )
  }

  if (!accounts || accounts.length === 0) {
    if (collapsed) {
      return (
        <div className="flex justify-center px-3 py-2">
          <Tooltip>
            <TooltipTrigger
              render={
                <Button
                  variant="outline"
                  size="icon-sm"
                  aria-label="Create trading account"
                  render={<Link href="/accounts" />}
                />
              }
            >
              <Plus className="size-4" />
            </TooltipTrigger>
            <TooltipContent side="right">
              No Trading Account — Create Account
            </TooltipContent>
          </Tooltip>
        </div>
      )
    }

    return (
      <div className="px-3 py-2">
        <Link
          href="/accounts"
          className="flex flex-col gap-1 rounded-lg border border-dashed border-border px-3 py-2.5 text-sm hover:bg-muted"
        >
          <span className="font-medium text-foreground">
            No Trading Account
          </span>
          <span className="flex items-center gap-1 text-xs text-primary">
            <Plus className="size-3" /> Create Account
          </span>
        </Link>
      </div>
    )
  }

  const trigger = (
    <button
      type="button"
      title={collapsed ? selectedAccount?.name : undefined}
      className={cn(
        "flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left outline-none hover:bg-muted focus-visible:ring-2 focus-visible:ring-ring/50",
        collapsed && "justify-center px-0 py-1.5"
      )}
    >
      <span className="flex size-7 shrink-0 items-center justify-center rounded-md bg-muted text-muted-foreground">
        <Wallet className="size-3.5" />
      </span>
      {!collapsed && selectedAccount && (
        <span className="flex min-w-0 flex-1 flex-col leading-tight">
          <span className="truncate text-sm font-medium text-foreground">
            {selectedAccount.name}
          </span>
          <span className="truncate text-xs text-muted-foreground">
            {formatBalance(selectedAccount)}
          </span>
        </span>
      )}
      {!collapsed && (
        <ChevronsUpDown className="size-3.5 shrink-0 text-muted-foreground" />
      )}
    </button>
  )

  return (
    <div className="px-2 py-1">
      <Popover>
        <PopoverTrigger render={trigger} />
        <PopoverContent align="start" className="w-72 p-1.5">
          <div className="px-2 py-1.5 text-xs font-medium text-muted-foreground">
            Select Account
          </div>
          <div className="flex flex-col gap-0.5">
            {accounts.map((account) => (
              <button
                key={account.id}
                type="button"
                onClick={() => setSelectedAccountId(account.id)}
                className="flex items-center gap-2 rounded-md px-2 py-1.5 text-left text-sm hover:bg-muted"
              >
                <span className="flex size-4 shrink-0 items-center justify-center">
                  {account.id === selectedAccount?.id && (
                    <Check className="size-3.5 text-primary" />
                  )}
                </span>
                <span className="flex min-w-0 flex-1 flex-col">
                  <span className="flex items-center gap-1.5 truncate font-medium text-foreground">
                    {account.name}
                    <Badge variant="outline" className="h-4 px-1 text-[10px]">
                      {accountTypeLabels[account.account_type]}
                    </Badge>
                  </span>
                  <span className="text-xs text-muted-foreground">
                    {formatBalance(account)}
                  </span>
                </span>
              </button>
            ))}
          </div>
          <Separator className="my-1.5" />
          <Link
            href="/accounts"
            className="flex items-center gap-2 rounded-md px-2 py-1.5 text-sm text-muted-foreground hover:bg-muted hover:text-foreground"
          >
            Manage Accounts
          </Link>
        </PopoverContent>
      </Popover>
    </div>
  )
}
