<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendAuthCodeNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        protected string $code,
        protected string $type
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $actionText = $this->type === 'registration' ? 'verificar seu cadastro' : 'completar seu login';
        
        return (new MailMessage)
            ->subject('Seu código de verificação - FinançasPessoais')
            ->greeting('Olá!')
            ->line("Você solicitou um código para {$actionText}.")
            ->line('Utilize o código de 6 dígitos abaixo:')
            ->line($this->code) // Em um cenário real, poderíamos estilizar isso como um botão ou caixa de destaque
            ->line('Este código expira em 10 minutos.')
            ->line('Se você não solicitou este código, nenhuma ação adicional é necessária.');
    }
}
