<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class AllowedEmailDomain implements ValidationRule
{
    /**
     * Domínios de e-mail gratuitos permitidos (whitelist).
     */
    public const ALLOWED_DOMAINS = [
        // Internacionais mais populares
        'gmail.com',
        'hotmail.com',
        'outlook.com',
        'yahoo.com',
        'live.com',
        'icloud.com',
        'protonmail.com',
        'proton.me',
        'mail.com',
        'aol.com',
        'yandex.com',
        'zoho.com',
        'gmx.com',
        'fastmail.com',
        'tutanota.com',
        'hey.com',
        // Brasileiros
        'yahoo.com.br',
        'uol.com.br',
        'bol.com.br',
        'terra.com.br',
        'ig.com.br',
        'globo.com',
        'r7.com',
        'zipmail.com.br',
        'click21.com.br',
        'oi.com.br',
        // Microsoft variantes
        'hotmail.com.br',
        'outlook.com.br',
        'live.com.br',
        // variantes Google
        'googlemail.com',
    ];

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $domain = substr(strrchr((string) $value, '@'), 1);

        if (!in_array(strtolower($domain), self::ALLOWED_DOMAINS, true)) {
            $fail('validation.allowed_email_domain')->translate();
        }
    }
}
