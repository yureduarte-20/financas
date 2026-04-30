<?php

namespace App\Telegram\Commands;

use App\Models\User;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Keyboard\Keyboard;

class StartCommand extends Command
{
    protected string $name = 'start';
    protected string $description = 'Comando para iniciar a aplicação';

    public function handle()
    {
        $update = $this->getUpdate();
        $chatId = $update->getChat()->id;
        $user = User::where('telegram_chat_id', $chatId)->first();
        if ($user) {
            return $this->replyWithMessage([
                'text' => "Olá {$user->name}, bem-vindo de volta app Finanças do yure!",
            ]);
        }

        $text = "<b>Bem-vindo ao Finanças!</b>\n\n" .
            "Para começar, você precisa ter uma conta em:" .
            "🌐 <a href='https://financas.yure.tec.br'>financas.yure.tec.br</a>\n\n" .
            "<i>Já possui cadastro?</i> Use o comando /sync para conectar sua conta.";

        $this->replyWithMessage([
            'text' => $text,
            'parse_mode' => 'html'
        ]);

    }
}
