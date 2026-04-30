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
        $url = env('APP_URL', '');

        $text = "<b>Bem-vindo ao Finanças!</b>\n\n" .
            "Para começar, você precisa ter uma conta em:" .
            "🌐 <a href=\"$url\">$url</a>\n\n" .
            "<i>Já possui cadastro?</i> Use o comando /sync para conectar sua conta.\n".
            "Utilize o comando /help para mais informações"
            ;

        $this->replyWithMessage([
            'text' => $text,
            'parse_mode' => 'html'
        ]);

    }
}
