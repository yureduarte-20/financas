<?php

namespace App\Telegram\Commands;

use App\Models\AuthCode;
use App\Models\User;
use App\Notifications\TelegramSyncAuthCodeNotification;
use Cache;
use Telegram\Bot\Commands\Command;

class SyncAccountCommand extends Command
{
    protected string $name = 'sync';
    protected string $description = 'Comando para sincronizar sua conta a este chat';
    protected string $pattern = "{email}";

    public function handle()
    {
        $chat_id = $this->getUpdate()->getChat()->id;
        if (User::where('telegram_chat_id', $chat_id)->exists()) {
            return $this->replyWithMessage([
                'text' => 'Você já possui uma conta sincronizada'
            ]);
        }
        $email = $this->argument(
            'email',
        );
        if (empty($email)) {
            return $this->replyWithMessage([
                'text' => "Você não especificou qual é seu email, precisamos dele para sincronizar",
            ]);
        }
        $user = User::whereEmail($email)->first();
        if (!$user) {
            return $this->replyWithMessage([
                'text' => "Não encontramos nenhum usuário com o email {$email}, por favor, verifique se o email está correto."
            ]);
        }

        $authCodeStr = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $authCode = AuthCode::create([
            'email' => $user->email,
            'code' => $authCodeStr,
            'type' => 'telegram_sync',
            'expires_at' => now()->addMinutes(10),
        ]);
        Cache::put('telegram_code_for_' . $chat_id, $authCode->id, now()->addMinutes(10));
        $user->notify(new TelegramSyncAuthCodeNotification($authCodeStr));

        return $this->replyWithMessage([
            'text' => "Enviamos um código de verificação para o email {$email}. Por favor, verifique sua caixa de entrada e responda com o comando:\n\n/sync SEU_CODIGO"
        ]);
    }
}
