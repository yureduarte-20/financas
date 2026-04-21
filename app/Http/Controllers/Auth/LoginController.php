<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(
        LoginRequest $request,
        \App\Actions\Auth\GenerateAuthCodeAction $generateCode
    ): RedirectResponse {
        $request->authenticate();

        // Validamos a senha, mas não permitimos o acesso ainda.
        // Pegamos o e-mail do request para o 2FA
        $email = $request->email;
        
        // Desloga o usuário se o Auth::attempt logou automaticamente (o LoginRequest costuma logar)
        Auth::logout();

        $generateCode->execute($email, 'login');

        session(['auth.email' => $email, 'auth.remember' => $request->boolean('remember')]);

        return redirect()->route('auth.verify.login');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
