<?php

namespace App\Livewire\Forms;

class UpdateCategoryForm extends AbstractActionForm
{
    public ?string $id = null;
    public string $name = '';
    public ?string $description = null;

    public function getAction(): \App\Actions\AbstractAction
    {
        return app()->make(\App\Actions\Category\UpdateCategoryAction::class);
    }
}
