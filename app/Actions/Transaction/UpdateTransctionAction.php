<?php

namespace App\Actions\Transaction;

use App\Actions\AbstractAction;
use App\Models\Transaction;
use Illuminate\Support\Facades\Gate;

class UpdateTransctionAction extends AbstractAction
{
    /**
     * @return array<string, \Illuminate\Validation\Rule|string>
     */
    public function rules(): array
    {
        return [
            'id' => 'required|uuid|exists:transactions,id',
            'type' => 'required|in:in,out',
            'value' => 'required|numeric|min:0',
            'description' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
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
        $transaction = Transaction::findOrFail($validated['id']);
        Gate::authorize('update', $transaction);
        return $transaction->update($validated);
    }
}
