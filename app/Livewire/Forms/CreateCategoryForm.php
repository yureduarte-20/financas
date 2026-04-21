<?php

namespace App\Livewire\Forms;

class CreateCategoryForm extends AbstractActionForm
{
    public $name;
    public $description;

    public function getAction(): \App\Actions\AbstractAction
    {
        return app()->make(\App\Actions\Category\CreateCategoryAction::class);
    }
}
