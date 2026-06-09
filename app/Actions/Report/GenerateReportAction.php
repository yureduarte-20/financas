<?php

namespace App\Actions\Report;

use App\Actions\AbstractAction;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class GenerateReportAction extends AbstractAction
{
    /**
     * Get the validation rules for the report query.
     *
     * @return array<string, \Illuminate\Validation\Rule|string>
     */
    public function rules(): array
    {
        return [
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'category_id' => [
                'nullable',
                'uuid',
                Rule::exists('categories', 'id')->where('user_id', Auth::id()),
            ],
            'type' => 'nullable|string|in:out,income',
        ];
    }

    /**
     * Execute the action to compile report data.
     *
     * @param array $input
     * @return array
     */
    public function execute(array $input): array
    {
        $validated = $this->validate($input);

        $query = Transaction::query()
            ->where('user_id', Auth::id());

        if (!empty($validated['start_date'])) {
            $query->where('expense_date', '>=', $validated['start_date']);
        }

        if (!empty($validated['end_date'])) {
            $query->where('expense_date', '<=', $validated['end_date']);
        }

        if (!empty($validated['category_id'])) {
            $query->where('category_id', $validated['category_id']);
        }

        if (!empty($validated['type'])) {
            $query->where('type', $validated['type']);
        }

        $transactions = $query->with('category')->orderBy('expense_date', 'desc')->get();

        $totalIncome = 0.0;
        $totalExpense = 0.0;

        foreach ($transactions as $transaction) {
            if ($transaction->type === 'income') {
                $totalIncome += (float) $transaction->value;
            } elseif ($transaction->type === 'out') {
                $totalExpense += (float) $transaction->value;
            }
        }

        $categoryBreakdown = $transactions->groupBy('category_id')->map(function ($items) {
            $firstItem = $items->first();
            $categoryName = $firstItem->category->name ?? 'Sem Categoria';
            $total = $items->sum(fn ($item) => (float) $item->value);
            return [
                'category_id' => $firstItem->category_id,
                'category_name' => $categoryName,
                'total' => $total,
                'count' => $items->count(),
            ];
        })->values()->toArray();

        return [
            'summary' => [
                'total_income' => $totalIncome,
                'total_expense' => $totalExpense,
                'balance' => $totalIncome - $totalExpense,
            ],
            'breakdown' => $categoryBreakdown,
            'transactions' => $transactions->toArray(),
        ];
    }
}
