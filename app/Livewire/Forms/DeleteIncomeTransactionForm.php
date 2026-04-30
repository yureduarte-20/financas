<?php

namespace App\Livewire\Forms;

class DeleteIncomeTransactionForm extends AbstractActionForm
{
    public ?string $id = null;

    public function getAction(): \App\Actions\AbstractAction
    {
        return app()->make(\App\Actions\Transaction\DeleteIncomeTransactionAction::class);
    }
}
