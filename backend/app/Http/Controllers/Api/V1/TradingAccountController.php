<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\TradingAccounts\StoreTradingAccountRequest;
use App\Http\Requests\TradingAccounts\UpdateTradingAccountRequest;
use App\Http\Resources\TradingAccountResource;
use App\Models\TradingAccount;
use App\Services\BalanceReconciliationService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TradingAccountController extends Controller
{
    public function __construct(private readonly BalanceReconciliationService $balances) {}

    public function index(Request $request)
    {
        $accounts = $request->user()->tradingAccounts()->orderBy('created_at')->get();

        return TradingAccountResource::collection($accounts);
    }

    public function store(StoreTradingAccountRequest $request): TradingAccountResource
    {
        $account = $request->user()->tradingAccounts()->create([
            ...$request->validated(),
            // New accounts start flat: current balance/equity mirror the initial deposit
            // until transactions or (later) trades move them.
            'current_balance' => $request->validated('initial_balance'),
            'current_equity' => $request->validated('initial_balance'),
        ]);

        return new TradingAccountResource($account);
    }

    public function show(Request $request, TradingAccount $account): TradingAccountResource
    {
        $this->authorize('view', $account);

        return new TradingAccountResource($account);
    }

    public function update(UpdateTradingAccountRequest $request, TradingAccount $account): TradingAccountResource
    {
        $account->update($request->validated());

        return new TradingAccountResource($account);
    }

    public function destroy(Request $request, TradingAccount $account): Response
    {
        $this->authorize('delete', $account);

        if ($request->boolean('force')) {
            if ($account->transactions()->exists()) {
                abort(409, 'Account has transactions and cannot be permanently deleted.');
            }

            $account->forceDelete();

            return response()->noContent();
        }

        $account->delete();

        return response()->noContent();
    }
}
