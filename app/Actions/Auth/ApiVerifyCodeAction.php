<?php

namespace App\Actions\Auth;

use App\Actions\AbstractAction;
use App\Models\AuthCode;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class ApiVerifyCodeAction extends AbstractAction
{
    /**
     * Get the validation rules that apply to the action.
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'code' => ['required', 'string', 'size:6'],
            'device_name' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * Execute the action.
     */
    public function execute(array $input): array
    {
        $validated = $this->validate($input);

        $authCode = AuthCode::where('email', $validated['email'])
            ->where('code', $validated['code'])
            ->where('type', 'api_login')
            ->whereNull('verified_at')
            ->first();

        if (!$authCode || $authCode->isExpired()) {
            throw ValidationException::withMessages([
                'code' => ['O código informado é inválido ou expirou.'],
            ]);
        }

        // Mark code as verified
        $authCode->update(['verified_at' => now()]);

        // Find user
        $user = User::where('email', $validated['email'])->firstOrFail();

        // Create Sanctum token
        $token = $user->createToken($validated['device_name'])->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }
}
