<?php

namespace App\Livewire\Forms;

class DeleteTransactionForm extends AbstractActionForm
{
    public ?string $id = null;

    public function getAction(): \App\Actions\AbstractAction
    {
        return app()->make(\App\Actions\Transaction\DeleteTransactionAction::class);
    }
}
