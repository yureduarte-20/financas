<?php

namespace App\Http\Controllers;

use App\Jobs\HandlerCommandsJob;
use Illuminate\Http\Request;
use Illuminate\Queue\Jobs\Job;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramWebhookController extends Controller
{
    public function handle($token, Request $request)
    {

        if (!$token || (config('telegram.bots.mybot.webhook_token') != $token)) {
            return response()->json([], 403);
        }
        $update = Telegram::commandsHandler(true);
        Log::info('telegram_webhook', ['update' => $update]);
        return response()->noContent();
    }
}
