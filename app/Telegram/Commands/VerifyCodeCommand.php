<?php

namespace App\Telegram\Commands;

use App\Models\AuthCode;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Telegram\Bot\Commands\Command;

class VerifyCodeCommand extends Command
{
    protected string $name = 'verify_code';
    protected string $description = 'Comando para verificar se o código enviado para o seu email é válido, após a confirmação a sincronização com sua conta será concluída';
    protected string $pattern = "{code: \d+}";
    public function handle()
    {
        $chatId = $this->getUpdate()->getChat()->id;
        $code = $this->argument(
            'code',
        );
        if (empty($code)) {
            return $this->replyWithMessage([
                'text' => "Você não especificou qual é o código, precisamos dele para sincronizar",
            ]);
        }
        $authCodeId = Cache::get('telegram_code_for_' . $chatId);
        if (!$authCodeId) {
            return $this->replyWithMessage([
                'text' => "Código expirado ou inválido",
            ]);
        }
        $authCode = AuthCode::where('id', $authCodeId)->first();
        if (!$authCode or $authCode->isExpired()) {
            return $this->replyWithMessage([
                'text' => "Código expirado ou inválido",
            ]);
        }
        $user = User::where('email', $authCode->email)->first();
        if (!$user) {
            return $this->replyWithMessage([
                'text' => "Usuário não encontrado",
            ]);
        }
        $user->telegram_chat_id = $chatId;
        $user->save();
        $authCode->delete();
        return $this->replyWithMessage([
            'text' => "Conta sincronizada com sucesso!",
        ]);
    }
}
