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

class VerifyLoginController extends Controller
{
    public function show(Request $request): View|RedirectResponse
    {
        $email = session('auth.email');

        if (!$email) {
            return redirect()->route('login');
        }

        return view('auth.verify-login', ['email' => $email]);
    }

    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $email = session('auth.email');
        $remember = session('auth.remember', false);

        $authCode = AuthCode::where('email', $email)
            ->where('code', $request->code)
            ->where('type', 'login')
            ->whereNull('verified_at')
            ->first();

        if (!$authCode || $authCode->isExpired()) {
            throw ValidationException::withMessages([
                'code' => 'O código informado é inválido ou expirou.',
            ]);
        }

        // Marca como verificado
        $authCode->update(['verified_at' => now()]);

        // Login definitivo
        $user = User::where('email', $email)->first();
        Auth::login($user, $remember);

        $request->session()->regenerate();
        session()->forget(['auth.email', 'auth.remember']);

        return redirect()->intended(route('dashboard', absolute: false));
    }

    public function resend(GenerateAuthCodeAction $generateCode): RedirectResponse
    {
        $email = session('auth.email');

        if (!$email) {
            return redirect()->route('login');
        }

        $generateCode->execute($email, 'login');

        return back()->with('status', 'code-sent');
    }
}
