<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramWebhookController extends Controller
{
    public function handle()
    {


        // Se você quiser usar a lógica de comandos manualmente:

        $update = Telegram::commandsHandler(true);



        Log::info('telegram_webhook', ['update' => $update]);
        return 'ok';
    }
}
