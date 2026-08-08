"use client"

import { Wallet } from "lucide-react"

import { PageHeader } from "@/components/layout/page-header"
import { Badge } from "@/components/ui/badge"
import { Card, CardContent } from "@/components/ui/card"
import { Skeleton } from "@/components/ui/skeleton"
import { useAccountsQuery } from "@/features/accounts/api/queries"
import { CreateAccountDialog } from "@/features/accounts/components/create-account-dialog"
import { accountTypeLabels } from "@/features/accounts/types/schema"
import { useAccountStore } from "@/lib/store/account-store"

function formatMoney(amount: string, currency: string) {
  return new Intl.NumberFormat("en-US", {
    style: "currency",
    currency: currency || "USD",
  }).format(Number(amount))
}

export default function AccountsPage() {
  const { data: accounts, isLoading } = useAccountsQuery()
  const { selectedAccountId, setSelectedAccountId } = useAccountStore()

  return (
    <div className="flex flex-1 flex-col">
      <PageHeader
        title="Accounts"
        description="Manage your trading accounts"
        actions={<CreateAccountDialog />}
      />

      <div className="flex flex-1 flex-col gap-4 p-4 sm:p-6">
        {isLoading && (
          <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            {Array.from({ length: 3 }).map((_, i) => (
              <Skeleton key={i} className="h-32 rounded-xl" />
            ))}
          </div>
        )}

        {!isLoading && accounts && accounts.length === 0 && (
          <Card className="border-dashed">
            <CardContent className="flex flex-col items-center gap-2 py-16 text-center">
              <Wallet className="size-6 text-muted-foreground" />
              <p className="font-medium text-foreground">
                No trading account
              </p>
              <p className="text-sm text-muted-foreground">
                Create your first trading account to start tracking balance
                and growth.
              </p>
            </CardContent>
          </Card>
        )}

        {!isLoading && accounts && accounts.length > 0 && (
          <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            {accounts.map((account) => {
              const isSelected =
                account.id === (selectedAccountId ?? accounts[0]?.id)

              return (
                <button
                  key={account.id}
                  type="button"
                  onClick={() => setSelectedAccountId(account.id)}
                  className="text-left"
                >
                  <Card
                    className={
                      isSelected
                        ? "ring-2 ring-primary"
                        : "hover:ring-1 hover:ring-border"
                    }
                  >
                    <CardContent className="flex flex-col gap-3">
                      <div className="flex items-start justify-between gap-2">
                        <span className="font-medium text-foreground">
                          {account.name}
                        </span>
                        <Badge variant="outline">
                          {accountTypeLabels[account.account_type]}
                        </Badge>
                      </div>
                      <div className="flex flex-col">
                        <span className="text-2xl font-semibold text-foreground">
                          {formatMoney(account.current_balance, account.currency)}
                        </span>
                        <span className="text-xs text-muted-foreground">
                          Starting {formatMoney(account.initial_balance, account.currency)}
                        </span>
                      </div>
                      {isSelected && (
                        <Badge variant="secondary" className="w-fit">
                          Selected
                        </Badge>
                      )}
                    </CardContent>
                  </Card>
                </button>
              )
            })}
          </div>
        )}
      </div>
    </div>
  )
}
