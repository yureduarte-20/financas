<?php

namespace App\Telegram\Commands;

use App\Actions\Auth\RegisterUserAction;
use App\Models\AuthCode;
use App\Models\User;
use App\Notifications\TelegramSyncAuthCodeNotification;
use App\Rules\AllowedEmailDomain;
use Cache;
use Hash;
use Str;
use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;

class CreateAccountCommand extends Command
{
    protected string $name = 'create_account';
    protected array $aliases = ['cadastro'];
    protected string $description = 'Cadastrar conta. Uso: /cadastro email@exemplo.com "Seu Nome"';
    protected string $pattern = '{email} {name:"[^"]+"|\S+}';

    public function handle()
    {
        $this->getTelegram()->setAsyncRequest(true);
        $email = $this->argument('email');
        $name = $this->argument('name');
        $password = Str::password(8);
        $chat_id = $this->getUpdate()->getChat()->id;
        if (empty($email)) {
            return $this->replyWithMessage([
                'text' => 'O email é obrigatório.'
            ]);
        }

        if (empty($name)) {
            return $this->replyWithMessage([
                'text' => 'O nome é obrigatório.'
            ]);
        }

        // Valida dominio de email (apenas emails pessoais/permitidos)
        $domain = substr(strrchr($email, '@'), 1);
        if (!in_array(strtolower($domain), AllowedEmailDomain::ALLOWED_DOMAINS, true)) {
            return $this->replyWithMessage([
                'text' => 'O cadastro é permitido apenas para e-mails pessoais (Gmail, Hotmail, Outlook, etc.). Não são aceitos e-mails corporativos.',
                'parse_mode' => 'HTML'
            ]);
        }
        $exists = User::where('email', $email)->orWhere('telegram_chat_id', $chat_id)->exists();
        if ($exists) {
            return $this->replyWithMessage([
                'text' => 'O email ' . $email . ' já está sendo utilizado ou o chat já está vinculado a uma conta.'
            ]);
        }



        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password)
        ]);
        $authCodeStr = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $authCode = AuthCode::create([
            'email' => $user->email,
            'code' => $authCodeStr,
            'type' => 'telegram_sync',
            'expires_at' => now()->addMinutes(10),
        ]);
        Cache::put('telegram_code_for_' . $chat_id, $authCode->id, now()->addMinutes(10));
        $user->notify(new TelegramSyncAuthCodeNotification($authCodeStr));
        $url = env('APP_URL', 'http://localhost:8000');
        $text = "<b>Conta cadastrada com sucesso! Sua senha é: </b><i>" . $password . "</i>\nVocê pode acessar a aplicação completa em: <a href=\"{$url}\">{$url}</a>\n";
        $text .= "Enviamos um código de verificação para o email {$email}. Por favor, verifique sua caixa de entrada e responda com o comando:\n\n";
        $text .= "<b>/verify_code <i>código</i></b>";
        $this->replyWithMessage([
            'text' => $text,
            'parse_mode' => 'HTML'
        ]);
    }
}
