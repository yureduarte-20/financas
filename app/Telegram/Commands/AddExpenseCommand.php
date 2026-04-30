<?php

namespace App\Telegram\Commands;

use App\Actions\Transaction\CreateExpenseTransactionAction;
use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AddExpenseCommand extends LoggedInCommand
{
    protected string $name = 'add_expense';
    protected array $aliases = ['gasto'];
    protected string $description = 'Registrar gasto: /gasto {valor} {"descrição"} {"categoria"} {data}';

    // O pulo do gato está aqui! Ensinamos o regex a aceitar aspas ou palavras soltas.
    protected string $pattern = '{valor:\S+} {description:"[^"]+"|\S+} {category:"[^"]+"|\S+} {data:\S+}';

    public function executeWithUserLoggedIn()
    {
        $rawValor = $this->argument('valor');

        if (!$rawValor) {
            return $this->replyWithMessage(['text' => '⚠️ Informe ao menos o valor. Ex: /gasto 15,50']);
        }

        Log::info('AddExpenseCommand - Argumentos Recebidos', $this->arguments);

        // 1. Sanitize e Validação do Valor
        $valor = str_replace(['R$', ' '], '', $rawValor);
        $valor = str_replace(',', '.', $valor);

        if (!is_numeric($valor)) {
            return $this->replyWithMessage(['text' => '❌ Valor inválido. Use o formato: 100,00']);
        }

        // 2. Tratamento de Descrição (Removemos as aspas que vêm do Regex)
        $rawDescription = $this->argument('description', 'Telegram: Sem descrição');
        $description = trim($rawDescription, '"\'');

        // 3. Tratamento de Categoria (Removemos as aspas)
        $rawCategory = $this->argument('category', 'Outros');
        $categoryName = $rawCategory ? trim($rawCategory, '"\'') : null;
        $categoryId = $this->findCategoryByName($categoryName)->id;

        // 4. Tratamento de Data
        $dataRaw = $this->argument('data');
        $dataParsed = $this->parseDate($dataRaw);
        $expenseDate = $dataParsed ?: now()->format('Y-m-d');

        // 5. Execução
        app()->make(CreateExpenseTransactionAction::class)->execute([
            'category_id' => $categoryId,
            'type' => 'out',
            'value' => $valor,
            'name' => "Despesa adicionada no telegram de R$ {$valor}",
            'description' => $description,
            'expense_date' => $expenseDate
        ]);

        return $this->replyWithMessage([
            'text' => '✅ Despesa de R$ ' . number_format((float) $valor, 2, ',', '.') . ' salva com sucesso!'
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