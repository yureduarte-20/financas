<?php

namespace App\Livewire\Forms;

class UpdateIncomeTransactionForm extends AbstractActionForm
{
    public ?string $id = null;
    public string $name = '';
    public ?string $description = null;
    public string $value = '';
    public ?string $expense_date = null;
    public ?string $category_id = null;

    public function getAction(): \App\Actions\AbstractAction
    {
        return app()->make(\App\Actions\Transaction\UpdateIncomeTransactionAction::class);
    }
}
