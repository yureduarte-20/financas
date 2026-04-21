<?php

namespace App\Actions\Auth;

use Illuminate\Support\Facades\Password;

class SendPasswordResetLinkAction
{
    /**
     * Send a password reset link to the given user.
     */
    public function execute(array $data): string
    {
        return Password::broker()->sendResetLink($data);
    }
}
