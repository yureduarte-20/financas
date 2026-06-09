<?php

namespace App\Actions\Auth;

use App\Actions\AbstractAction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ApiLoginAction extends AbstractAction
{
    /**
     * Get the validation rules that apply to the action.
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Execute the action.
     */
    public function execute(array $input): array
    {
        $validated = $this->validate($input);

        $throttleKey = Str::transliterate(
            Str::lower($validated['email']) . '|' . request()->ip()
        );

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'email' => [__('auth.throttle', [
                    'seconds' => $seconds,
                    'minutes' => ceil($seconds / 60),
                ])],
            ]);
        }

        // Validate credentials without logging in
        if (!Auth::validate($validated)) {
            RateLimiter::hit($throttleKey);
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        RateLimiter::clear($throttleKey);

        // Generate and send 2FA code
        app(GenerateAuthCodeAction::class)->execute($validated['email'], 'api_login');

        return [
            'email' => $validated['email'],
        ];
    }
}
