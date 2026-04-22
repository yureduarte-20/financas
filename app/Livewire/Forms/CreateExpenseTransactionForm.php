<?php

namespace App\Livewire\Forms;
use Livewire\Attributes\Locked;

class CreateExpenseTransactionForm extends AbstractActionForm
{
    public string $name = '';
    public ?string $description = null;
    public string $value = '';
    public ?string $expense_date = null;
    public ?string $category_id = null;
    #[Locked]
    public ?string $document_id = null;

    public function getAction(): \App\Actions\AbstractAction
    {
        return app()->make(\App\Actions\Transaction\CreateTransactionAction::class);
    }
}
