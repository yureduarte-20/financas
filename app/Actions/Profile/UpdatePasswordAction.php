<?php

namespace App\Actions\Profile;

use App\Actions\AbstractAction;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UpdatePasswordAction extends AbstractAction
{
    public function rules(): array
    {
        return [
            'current_password' => 'required|string|current_password',
            'password' => 'required|string|min:8|confirmed',
        ];
    }

    public function attributes(): array
    {
        return [
            'current_password' => 'senha atual',
            'password' => 'nova senha',
        ];
    }

    public function execute(array $input): mixed
    {
        $validated = $this->validate($input);

        /** @var User $user */
        $user = Auth::user();
        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return $user->refresh();
    }
}
