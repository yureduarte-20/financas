<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\GenerateAuthCodeAction;
use App\Http\Controllers\Controller;
use App\Models\AuthCode;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class VerifyEmailController extends Controller
{
    public function show(Request $request): View|RedirectResponse
    {
        $email = session('auth.email');

        if (!$email) {
            return redirect()->route('register');
        }

        return view('auth.verify-email', ['email' => $email]);
    }

    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $email = session('auth.email');

        $authCode = AuthCode::where('email', $email)
            ->where('code', $request->code)
            ->where('type', 'registration')
            ->whereNull('verified_at')
            ->first();

        if (!$authCode || $authCode->isExpired()) {
            throw ValidationException::withMessages([
                'code' => 'O código informado é inválido ou expirou.',
            ]);
        }

        // Marca como verificado
        $authCode->update(['verified_at' => now()]);

        // Ativa o usuário
        $user = User::where('email', $email)->first();
        $user->update(['email_verified_at' => now()]);

        // Login
        Auth::login($user);

        session()->forget('auth.email');

        return redirect()->route('dashboard');
    }

    public function resend(GenerateAuthCodeAction $generateCode): RedirectResponse
    {
        $email = session('auth.email');

        if (!$email) {
            return redirect()->route('register');
        }

        $generateCode->execute($email, 'registration');

        return back()->with('status', 'code-sent');
    }
}
