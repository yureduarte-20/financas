<?php

namespace App\Telegram\Commands;

use App\Models\User;
use Exception;
use Telegram\Bot\Commands\Command;

abstract class LoggedInCommand extends Command
{

    public function handle()
    {

        $user = $this->getUser();
        if (!$user) {
            $this->replyWithMessage([
                'text' => 'Você não está logado no sistema. Para logar, utilize o comando /sync <email>'
            ]);
            return;
        }
        try {
            return $this->executeWithUserLoggedIn();
        } catch (Exception $e) {
            report($e);
            $this->replyWithMessage([
                'text' => 'Não foi possível processar sua mensagem agora, por favor, tente mais tarde.'
            ]);
        }
    }
    public abstract function executeWithUserLoggedIn();
    protected function getUser()
    {
        $chatId = $this->getUpdate()->getChat()->id;
        return User::where('telegram_chat_id', $chatId)->first();
    }
}
