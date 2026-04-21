<?php

namespace App\Actions\Auth;

use App\Models\AuthCode;
use App\Notifications\SendAuthCodeNotification;
use Illuminate\Support\Facades\Notification;

class GenerateAuthCodeAction
{
    /**
     * Generate and send a verification code.
     */
    public function execute(string $email, string $type): void
    {
        // Limpa códigos anteriores do mesmo tipo para este e-mail
        AuthCode::where('email', $email)
            ->where('type', $type)
            ->whereNull('verified_at')
            ->delete();

        // Gera código de 6 dígitos
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Salva no banco
        AuthCode::create([
            'email' => $email,
            'code' => $code,
            'type' => $type,
            'expires_at' => now()->addMinutes(10),
        ]);

        // Envia notificação
        Notification::route('mail', $email)
            ->notify(new SendAuthCodeNotification($code, $type));
    }
}
