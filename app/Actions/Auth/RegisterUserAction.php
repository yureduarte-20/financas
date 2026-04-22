<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;

class RegisterUserAction
{
    /**
     * Handle the registration of a new user.
     */
    public function execute(array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
        $user->categories()->create([
            'name' => 'Outros'
        ]);
        event(new Registered($user));

        return $user;
    }
}
