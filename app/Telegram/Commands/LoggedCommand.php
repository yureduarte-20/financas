<?php

namespace App\Telegram\Commands;

use App\Models\User;
use Telegram\Bot\Commands\Command;

abstract class LoggedCommand extends Command
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
        return $this->executeWithUserLoggedIn();
    }
    public abstract function executeWithUserLoggedIn();
    protected function getUser()
    {
        $chatId = $this->getUpdate()->getChat()->id;
        return User::where('telegram_chat_id', $chatId)->first();
    }
}
