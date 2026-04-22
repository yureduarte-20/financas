<?php

namespace App\Livewire\Forms;

class UpdateProfileForm extends AbstractActionForm
{
    public string $name = '';
    public string $email = '';

    public function getAction(): \App\Actions\AbstractAction
    {
        return app()->make(\App\Actions\Profile\UpdateProfileAction::class);
    }
}
