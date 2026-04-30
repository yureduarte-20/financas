<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Log;
use Telegram\Bot\Laravel\Facades\Telegram;

class HandlerCommandsJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $update = Telegram::commandsHandler(true);
        Log::info('telegram_webhook', ['update' => $update]);
    }
}
