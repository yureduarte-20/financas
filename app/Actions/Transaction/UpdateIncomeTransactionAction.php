<?php

namespace App\Actions\Transaction;

use App\Actions\AbstractAction;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateIncomeTransactionAction extends AbstractAction
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'uuid',
                Rule::exists('transactions', 'id')
                    ->where('user_id', Auth::id())
                    ->where('type', 'income'),
            ],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'value' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'category_id' => [
                'required',
                'uuid',
                Rule::exists('categories', 'id')->where('user_id', Auth::id()),
            ],
        ];
    }

    public function execute(array $input): mixed
    {
        $validated = $this->validate($input);

        $transaction = Transaction::query()
            ->where('type', 'income')
            ->findOrFail($validated['id']);

        Gate::authorize('update', $transaction);

        $transaction->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'value' => $validated['value'],
            'expense_date' => $validated['expense_date'],
            'category_id' => $validated['category_id'],
        ]);

        return $transaction->refresh();
    }
}
