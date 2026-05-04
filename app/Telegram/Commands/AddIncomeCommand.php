<?php

namespace App\Telegram\Commands;

use App\Actions\Transaction\CreateExpenseTransactionAction;
use App\Actions\Transaction\CreateIncomeTransactionAction;
use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Log;
use Telegram\Bot\Commands\Command;

class AddIncomeCommand extends LoggedInCommand
{

    protected string $name = 'add_income';
    protected array $aliases = ['receita'];
    protected string $description = 'Registrar receita: /receita {valor} {"descrição"} {"categoria"} {data}';

    // O pulo do gato está aqui! Ensinamos o regex a aceitar aspas ou palavras soltas.
    protected string $pattern = '{valor:\S+} {description:"[^"]+"|\S+} {category:"[^"]+"|\S+} {data:\S+}';

    public function executeWithUserLoggedIn()
    {
        $user = $this->getUser();
        $rawValor = $this->argument('valor');

        if (!$rawValor) {
            return $this->replyWithMessage(['text' => '⚠️ Informe ao menos o valor. Ex: /receita 15,50']);
        }

        Log::info('AddIncome Command - Argumentos Recebidos', $this->arguments);

        // 1. Sanitize e Validação do Valor
        $amount = str_replace(['R$', ' '], '', $rawValor);
        $amount = str_replace(',', '.', $amount);

        if (!is_numeric($amount)) {
            return $this->replyWithMessage(['text' => '❌ Valor inválido. Use o formato: 100,00']);
        }

        // 2. Tratamento de Descrição (Removemos as aspas que vêm do Regex)
        $rawDescription = $this->argument('description', 'Telegram: Sem descrição');
        $description = trim($rawDescription, '"\'');

        // 3. Tratamento de Categoria (Removemos as aspas)
        $rawCategory = $this->argument('category', 'Outros');
        $categoryName = trim($rawCategory, '"\'');
        $categoryId = $this->findCategoryByName($categoryName)->id;

        // 4. Tratamento de Data
        $dataRaw = $this->argument('data');
        $dataParsed = $this->parseDate($dataRaw);
        $incomeDate = $dataParsed ?: now()->format('Y-m-d');
        $action = app()->make(CreateIncomeTransactionAction::class);
        try {
            // 5. Execução
            $action->execute([
                'category_id' => $categoryId,
                'type' => 'income',
                'value' => $amount,
                'name' => "Receita adicionada no telegram de R$ {$amount}",
                'description' => $description,
                'expense_date' => $incomeDate
            ]);
        } catch (ValidationException $ex) {
            Log::warning('validation errors', $ex->errors());
            throw $ex;
        }

        return $this->replyWithMessage([
            'text' => '✅ Receita de R$ ' . number_format((float) $amount, 2, ',', '.') . ' salva com sucesso!'
        ]);
    }

    private function findCategoryByName(string $name)
    {
        return Category::firstOrCreate([
            'user_id' => $this->getUser()->id,
            'name' => ucfirst(strtolower($name))
        ]);
    }

    private function parseDate($date)
    {
        if (!$date)
            return null;

        try {
            return Carbon::createFromFormat('d/m/Y', $date)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
}
