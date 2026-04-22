<?php

namespace App\Livewire\Forms;

class UpdatePasswordForm extends AbstractActionForm
{
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function getAction(): \App\Actions\AbstractAction
    {
        return app()->make(\App\Actions\Profile\UpdatePasswordAction::class);
    }
}
