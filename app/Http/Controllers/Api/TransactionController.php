<?php

namespace App\Http\Controllers\Api;

use App\Actions\Transaction\CreateExpenseTransactionAction;
use App\Actions\Transaction\CreateIncomeTransactionAction;
use App\Actions\Transaction\DeleteIncomeTransactionAction;
use App\Actions\Transaction\DeleteTransactionAction;
use App\Actions\Transaction\UpdateExpenseTransactionAction;
use App\Actions\Transaction\UpdateIncomeTransactionAction;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TransactionController extends Controller
{
    /**
     * Display a listing of the transactions for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Transaction::class);

        $query = $request->user()->transactions()->with('category');

        if ($request->has('type')) {
            $type = $request->query('type');
            if (in_array($type, ['out', 'income'])) {
                $query->where('type', $type);
            }
        }

        $transactions = $query->orderBy('expense_date', 'desc')->get();

        return response()->json([
            'data' => $transactions,
        ], 200);
    }

    /**
     * Store a newly created transaction.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|string|in:out,income',
        ]);

        $type = $request->input('type');

        $action = match ($type) {
            'income' => app(CreateIncomeTransactionAction::class),
            'out' => app(CreateExpenseTransactionAction::class),
        };

        $transaction = $action->execute($request->all());

        return response()->json([
            'message' => 'Transação criada com sucesso.',
            'data' => $transaction,
        ], 201);
    }

    /**
     * Display the specified transaction.
     */
    public function show(Transaction $transaction): JsonResponse
    {
        Gate::authorize('view', $transaction);

        return response()->json([
            'data' => $transaction->load('category'),
        ], 200);
    }

    /**
     * Update the specified transaction.
     */
    public function update(Request $request, Transaction $transaction): JsonResponse
    {
        Gate::authorize('update', $transaction);

        $input = array_merge($request->all(), ['id' => $transaction->id]);

        $action = match ($transaction->type) {
            'income' => app(UpdateIncomeTransactionAction::class),
            'out' => app(UpdateExpenseTransactionAction::class),
        };

        $updatedTransaction = $action->execute($input);

        return response()->json([
            'message' => 'Transação atualizada com sucesso.',
            'data' => $updatedTransaction,
        ], 200);
    }

    /**
     * Remove the specified transaction from storage.
     */
    public function destroy(Transaction $transaction): JsonResponse
    {
        Gate::authorize('delete', $transaction);

        $input = ['id' => $transaction->id];

        $action = match ($transaction->type) {
            'income' => app(DeleteIncomeTransactionAction::class),
            'out' => app(DeleteTransactionAction::class),
        };

        $action->execute($input);

        return response()->json([
            'message' => 'Transação excluída com sucesso.',
        ], 200);
    }
}
