<?php

namespace App\Telegram\Commands;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Log;
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
            Auth::login($user);
            return $this->executeWithUserLoggedIn();
        } catch (Exception $e) {
            report($e);
            $text = $e->getTraceAsString();
            Log::error('Erro ao processar comando no telegram:' . $text);
            $this->replyWithMessage([
                'text' => 'Não foi possível processar sua mensagem agora, por favor, tente mais tarde.'

            ]);
        } finally {
            Auth::logout();
        }
    }
    public abstract function executeWithUserLoggedIn();
    protected function getUser()
    {
        $chatId = $this->getUpdate()->getChat()->id;
        return User::where('telegram_chat_id', $chatId)->first();
    }
}
