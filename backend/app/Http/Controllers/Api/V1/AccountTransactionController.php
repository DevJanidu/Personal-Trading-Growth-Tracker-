<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\AccountTransactions\StoreAccountTransactionRequest;
use App\Http\Resources\AccountTransactionResource;
use App\Models\TradingAccount;
use App\Services\BalanceReconciliationService;
use Illuminate\Http\Request;

class AccountTransactionController extends Controller
{
    public function __construct(private readonly BalanceReconciliationService $balances) {}

    public function index(Request $request, TradingAccount $account)
    {
        $this->authorize('view', $account);

        $transactions = $account->transactions()
            ->when($request->filled('type'), fn ($query) => $query->where('type', $request->string('type')))
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('transaction_date', '>=', $request->date('date_from')))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('transaction_date', '<=', $request->date('date_to')))
            ->orderByDesc('transaction_date')
            ->paginate($request->integer('per_page', 25));

        return AccountTransactionResource::collection($transactions);
    }

    public function store(StoreAccountTransactionRequest $request, TradingAccount $account): AccountTransactionResource
    {
        $this->authorize('view', $account);

        $transaction = $account->transactions()->create($request->validated());

        $this->balances->reconcile($account);

        return new AccountTransactionResource($transaction);
    }
}
