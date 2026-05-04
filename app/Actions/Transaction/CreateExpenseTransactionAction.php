<?php

namespace App\Actions\Transaction;

use App\Actions\AbstractAction;
use App\Enum\TransactionStatusEnum;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class CreateExpenseTransactionAction extends AbstractAction
{
    /**
     * @return array<string, \Illuminate\Validation\Rule|string>
     */
    public function rules(): array
    {
        return [
            'value' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
            'expense_date' => 'required|date',
            'document_id' => 'nullable|uuid|exists:documents,id',
            'category_id' => [
                'required',
                'uuid',
                Rule::exists('categories', 'id')->where('user_id', Auth::id()),
            ],
            'name' => 'required|string|max:255',
        ];
    }

    /**
     * Execute the action.
     */
    public function execute(array $input): mixed
    {
        $validated = $this->validate($input);
        $validated['type'] ??= 'out';
        Gate::authorize('create', Transaction::class);

        return Transaction::create([
            ...$validated,
            'status' => TransactionStatusEnum::PUBLISHED,
            'user_id' => Auth::user()->id,
        ]);
    }
}
