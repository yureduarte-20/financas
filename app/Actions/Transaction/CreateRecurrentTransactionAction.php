<?php

namespace App\Actions\Transaction;

use App\Actions\AbstractAction;
use App\Models\RecurrentTransaction;

class CreateRecurrentTransactionAction extends AbstractAction
{
    /**
     * @return array<string, \Illuminate\Validation\Rule|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|min:3|max:255',
            'description' => 'nullable|string|min:3',
            'category_id' => 'required|uuid|exists:categories,id',
            'value' => 'required|numeric|min:1'
        ];
    }

    /**
     * Execute the action.
     */
    public function execute(array $input): mixed
    {
        $validated = $this->validate($input);
        $validated['user_id'] = auth()->user()->id;
        return RecurrentTransaction::create($validated);
    }
}
