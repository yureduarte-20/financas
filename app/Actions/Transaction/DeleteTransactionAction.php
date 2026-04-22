<?php

namespace App\Actions\Transaction;

use App\Actions\AbstractAction;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class DeleteTransactionAction extends AbstractAction
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'uuid',
                Rule::exists('transactions', 'id')
                    ->where('user_id', Auth::id())
                    ->where('type', 'out'),
            ],
        ];
    }

    public function execute(array $input): mixed
    {
        $validated = $this->validate($input);

        $transaction = Transaction::query()
            ->where('type', 'out')
            ->findOrFail($validated['id']);

        Gate::authorize('delete', $transaction);

        return $transaction->delete();
    }
}
