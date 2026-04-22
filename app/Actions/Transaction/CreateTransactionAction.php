<?php

namespace App\Actions\Transaction;

use App\Actions\AbstractAction;
use App\Enum\TransactionStatusEnum;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class CreateTransactionAction extends AbstractAction
{
    /**
     * @return array<string, \Illuminate\Validation\Rule|string>
     */
    public function rules(): array
    {
        return [
            'type' => 'required|in:in,out',
            'value' => 'required|numeric|min:0',
            'description' => 'required|string|max:255',
            'document_id' => 'nullable|exists:documents,id',
            'category_id' => 'required|exists:categories,id',
            'status' => 'required|in:published,review',
            'name' => 'required|string|max:255',
        ];
    }

    /**
     * Execute the action.
     */
    public function execute(array $input): mixed
    {
        $validated = $this->validate($input);
        $validated['user_id'] = Auth::user()->id;
        $validated['status'] = TransactionStatusEnum::PUBLISHED;
        Gate::authorize('create', Transaction::class);
        return Transaction::create($validated);
    }
}
