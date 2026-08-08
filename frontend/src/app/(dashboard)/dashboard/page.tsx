"use client"

import { NotebookTabs, Plus, Wallet } from "lucide-react"

import { PageHeader } from "@/components/layout/page-header"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Skeleton } from "@/components/ui/skeleton"
import { useAccountsQuery } from "@/features/accounts/api/queries"
import { useAccountStore } from "@/lib/store/account-store"

function formatMoney(amount: string, currency: string) {
  return new Intl.NumberFormat("en-US", {
    style: "currency",
    currency: currency || "USD",
  }).format(Number(amount))
}

export default function DashboardPage() {
  const { data: accounts, isLoading } = useAccountsQuery()
  const { selectedAccountId } = useAccountStore()
  const account =
    accounts?.find((item) => item.id === selectedAccountId) ?? accounts?.[0]

  return (
    <div className="flex flex-1 flex-col">
      <PageHeader
        title="Dashboard"
        description={
          account
            ? `${account.name} — trading performance overview`
            : "Trading performance overview"
        }
      />

      <div className="flex flex-1 flex-col gap-6 p-4 sm:p-6">
        {isLoading ? (
          <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            {Array.from({ length: 4 }).map((_, i) => (
              <Skeleton key={i} className="h-24 rounded-xl" />
            ))}
          </div>
        ) : account ? (
          <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <Card>
              <CardHeader className="pb-1">
                <CardTitle className="text-sm font-medium text-muted-foreground">
                  Current Balance
                </CardTitle>
              </CardHeader>
              <CardContent className="text-2xl font-semibold text-foreground">
                {formatMoney(account.current_balance, account.currency)}
              </CardContent>
            </Card>
            <Card>
              <CardHeader className="pb-1">
                <CardTitle className="text-sm font-medium text-muted-foreground">
                  Starting Balance
                </CardTitle>
              </CardHeader>
              <CardContent className="text-2xl font-semibold text-foreground">
                {formatMoney(account.initial_balance, account.currency)}
              </CardContent>
            </Card>
            <Card>
              <CardHeader className="pb-1">
                <CardTitle className="text-sm font-medium text-muted-foreground">
                  Total Trades
                </CardTitle>
              </CardHeader>
              <CardContent className="text-2xl font-semibold text-foreground">
                0
              </CardContent>
            </Card>
            <Card>
              <CardHeader className="pb-1">
                <CardTitle className="text-sm font-medium text-muted-foreground">
                  Win Rate
                </CardTitle>
              </CardHeader>
              <CardContent className="text-2xl font-semibold text-muted-foreground">
                —
              </CardContent>
            </Card>
          </div>
        ) : null}

        <Card className="flex flex-1 items-center justify-center py-16">
          <CardContent className="flex flex-col items-center gap-3 text-center">
            <span className="flex size-12 items-center justify-center rounded-full bg-muted text-muted-foreground">
              <NotebookTabs className="size-5" />
            </span>
            <div className="flex flex-col gap-1">
              <p className="font-medium text-foreground">No trades yet.</p>
              <p className="max-w-sm text-sm text-muted-foreground">
                Add your first trade to start building your performance
                history.
              </p>
            </div>
            <span className="mt-1 inline-flex items-center gap-1.5 rounded-lg bg-muted px-3 py-1.5 text-sm text-muted-foreground">
              <Plus className="size-3.5" /> Trade journaling is coming soon
            </span>
          </CardContent>
        </Card>

        {!isLoading && !account && (
          <Card className="border-dashed">
            <CardContent className="flex flex-col items-center gap-2 py-10 text-center">
              <Wallet className="size-6 text-muted-foreground" />
              <p className="font-medium text-foreground">
                No Trading Account
              </p>
              <p className="text-sm text-muted-foreground">
                Create a trading account to start tracking your performance.
              </p>
            </CardContent>
          </Card>
        )}
      </div>
    </div>
  )
}
