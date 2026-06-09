<?php

namespace App\Actions\Auth;

use App\Actions\AbstractAction;
use App\Models\User;

class ApiRegisterAction extends AbstractAction
{
    /**
     * Get the validation rules that apply to the action.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    /**
     * Execute the action.
     */
    public function execute(array $input): array
    {
        $validated = $this->validate($input);

        // Reuse the project's native RegisterUserAction
        $user = app(RegisterUserAction::class)->execute($validated);

        // Generate email verification code for the registered user
        app(GenerateAuthCodeAction::class)->execute($user->email, 'registration');

        return [
            'user' => $user,
        ];
    }
}
