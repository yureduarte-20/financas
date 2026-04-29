<?php

namespace App\Telegram\Commands;

use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;

class HelpCommand extends Command
{
    protected string $name = 'help';
    protected string $description = 'Lista os comandos disponíveis';

    public function handle()
    {
        $this->replyWithChatAction(['action' => Actions::TYPING]);

        $commands = $this->getTelegram()->getCommands();

        // Cabeçalho com Emoji e Negrito
        $response = "<b>🤖 Comandos Disponíveis</b>" . PHP_EOL . PHP_EOL;
        $response .= "Aqui está a lista do que eu posso fazer por você:" . PHP_EOL . PHP_EOL;

        foreach ($commands as $name => $command) {
            // Formatação: /comando - Descrição com o comando em negrito
            $response .= sprintf("🔹 /%s - <i>%s</i>" . PHP_EOL, $name, $command->getDescription());
        }

        $response .= PHP_EOL . "📌 <i>Dica: Digite o comando para iniciar.</i>";

        $this->replyWithMessage([
            'text' => $response,
            'parse_mode' => 'HTML' // Fundamental para o Telegram interpretar as tags
        ]);
    }
}
