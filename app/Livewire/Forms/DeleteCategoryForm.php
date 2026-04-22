<?php

namespace App\Livewire\Forms;

class DeleteCategoryForm extends AbstractActionForm
{
    public ?string $id = null;

    public function getAction(): \App\Actions\AbstractAction
    {
        return app()->make(\App\Actions\Category\DeleteCategoryAction::class);
    }
}
