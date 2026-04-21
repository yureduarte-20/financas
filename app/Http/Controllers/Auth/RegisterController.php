<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\RegisterUserAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisterController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(
        RegisterRequest $request,
        RegisterUserAction $action,
        \App\Actions\Auth\GenerateAuthCodeAction $generateCode
    ): RedirectResponse {
        $user = $action->execute($request->validated());

        $generateCode->execute($user->email, 'registration');

        session(['auth.email' => $user->email]);

        return redirect()->route('auth.verify.email');
    }
}
