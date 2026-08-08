"use client"

import { useState } from "react"
import { zodResolver } from "@hookform/resolvers/zod"
import { useForm } from "react-hook-form"
import { Plus } from "lucide-react"
import { toast } from "sonner"

import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import {
  Dialog,
  DialogContent,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from "@/components/ui/dialog"
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select"
import {
  Form,
  FormControl,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from "@/components/ui/form"
import { useCreateAccountMutation } from "@/features/accounts/api/mutations"
import {
  accountTypeLabels,
  accountTypes,
  createTradingAccountSchema,
  type CreateTradingAccountFormInput,
  type CreateTradingAccountInput,
} from "@/features/accounts/types/schema"
import { useAccountStore } from "@/lib/store/account-store"
import { getErrorMessage, getValidationErrors } from "@/lib/api/errors"

export function CreateAccountDialog() {
  const [open, setOpen] = useState(false)
  const createAccount = useCreateAccountMutation()
  const setSelectedAccountId = useAccountStore((s) => s.setSelectedAccountId)

  const form = useForm<
    CreateTradingAccountFormInput,
    unknown,
    CreateTradingAccountInput
  >({
    resolver: zodResolver(createTradingAccountSchema),
    defaultValues: {
      name: "",
      account_type: "personal_live",
      broker: "",
      currency: "USD",
      initial_balance: 0,
      account_created_date: new Date().toISOString().slice(0, 10),
    },
  })

  function onSubmit(values: CreateTradingAccountInput) {
    createAccount.mutate(values, {
      onSuccess: (account) => {
        setSelectedAccountId(account.id)
        toast.success(`${account.name} created`)
        form.reset()
        setOpen(false)
      },
      onError: (error) => {
        const fieldErrors = getValidationErrors(error)

        if (fieldErrors) {
          for (const [field, messages] of Object.entries(fieldErrors)) {
            form.setError(field as keyof CreateTradingAccountFormInput, {
              message: messages[0],
            })
          }
          return
        }

        toast.error(getErrorMessage(error, "Unable to create account."))
      },
    })
  }

  return (
    <Dialog open={open} onOpenChange={setOpen}>
      <DialogTrigger render={<Button />}>
        <Plus className="size-4" /> Add Account
      </DialogTrigger>
      <DialogContent className="sm:max-w-md">
        <DialogHeader>
          <DialogTitle>Create Trading Account</DialogTitle>
        </DialogHeader>
        <Form {...form}>
          <form
            onSubmit={form.handleSubmit(onSubmit)}
            className="flex flex-col gap-4"
          >
            <FormField
              control={form.control}
              name="name"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Account Name</FormLabel>
                  <FormControl>
                    <Input placeholder="Personal Live" {...field} />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />
            <FormField
              control={form.control}
              name="account_type"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Account Type</FormLabel>
                  <Select value={field.value} onValueChange={field.onChange}>
                    <FormControl>
                      <SelectTrigger className="w-full">
                        <SelectValue placeholder="Select a type" />
                      </SelectTrigger>
                    </FormControl>
                    <SelectContent>
                      {accountTypes.map((type) => (
                        <SelectItem key={type} value={type}>
                          {accountTypeLabels[type]}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                  <FormMessage />
                </FormItem>
              )}
            />
            <div className="grid grid-cols-2 gap-4">
              <FormField
                control={form.control}
                name="currency"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Currency</FormLabel>
                    <FormControl>
                      <Input placeholder="USD" maxLength={3} {...field} />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />
              <FormField
                control={form.control}
                name="initial_balance"
                render={({ field: { value, ...field } }) => (
                  <FormItem>
                    <FormLabel>Initial Balance</FormLabel>
                    <FormControl>
                      <Input
                        type="number"
                        step="0.01"
                        min="0"
                        value={typeof value === "number" ? value : (value as string) ?? ""}
                        {...field}
                      />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />
            </div>
            <FormField
              control={form.control}
              name="broker"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Broker (optional)</FormLabel>
                  <FormControl>
                    <Input placeholder="e.g. IC Markets" {...field} />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />
            <FormField
              control={form.control}
              name="account_created_date"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Account Opened</FormLabel>
                  <FormControl>
                    <Input type="date" {...field} />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />
            <DialogFooter className="-mx-4 -mb-4 mt-2">
              <Button type="submit" disabled={createAccount.isPending}>
                Create Account
              </Button>
            </DialogFooter>
          </form>
        </Form>
      </DialogContent>
    </Dialog>
  )
}
