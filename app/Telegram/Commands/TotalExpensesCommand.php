<?php

namespace App\Telegram\Commands;

use App\Models\Transaction;



class TotalExpensesCommand extends LoggedCommand
{
    protected string $name = 'expenses';
    protected string $description = 'Lista todas as despesas';

    public function executeWithUserLoggedIn()
    {
        $user = $this->getUser();
        $amount = Transaction::where('user_id', $user->id)->where('type', 'out')->sum('value');
        return $this->replyWithMessage([
            'text' => "No total você já gastou: R$ " . number_format($amount, 2, ',', '.')
        ]);
    }
}
