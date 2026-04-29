<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TelegramSyncAuthCodeNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected string $code
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Seu código de sincronização do Telegram - FinançasPessoais')
            ->greeting('Olá!')
            ->line('Você solicitou a sincronização da sua conta com o Telegram.')
            ->line('Utilize o código de 6 dígitos abaixo no seu aplicativo do Telegram:')
            ->line($this->code)
            ->line('Envie o comando `/sync ' . $notifiable->email . ' ' . $this->code . '` no bot do Telegram.')
            ->line('Este código expira em 10 minutos.')
            ->line('Se você não solicitou este código, ignore este e-mail.');
    }
}
